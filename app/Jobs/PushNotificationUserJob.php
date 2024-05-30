<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Models\ConfigNotification;
use App\Models\CustomerDeviceToken;
use App\Models\NotificationCustomer;
use App\Models\NotificationUser;
use App\Models\UserDeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PushNotificationUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store_id;
    protected $user_id;
    protected $content;
    protected $title;
    protected $type;
    protected $references_value;
    protected $branch_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $store_id,
        $user_id,
        $title,
        $content,
        $type,
        $references_value,
        $branch_id
    ) {


        $this->store_id = $store_id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->references_value = $references_value;
        $this->branch_id = $branch_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notification_unread = 0;

        $notification_unread = NotificationUser::where('store_id', $this->store_id  ?? null)
            ->where(function ($query) {
                $query->where(
                    'branch_id',
                    $this->branch_id
                )->orWhere('branch_id', "=", null);
            })
            ->where('unread', true)->count();

        $notification_unread = $notification_unread + 1;


        $deviceTokens = UserDeviceToken::where('user_id',  $this->user_id)
            ->pluck('device_token')
            ->toArray();

        $deviceTokens = array_unique($deviceTokens);

        $data = [
            'body' => $this->content,
            'title' =>  $this->title,
            'type' => $this->type,
            'references_value' => $this->references_value,
            'badge' => (int)$notification_unread
        ];

        $key = Helper::getRandomOrderString();

        $this->subscribeTopic($deviceTokens, $key);
        $this->sendNotification($data, $key);
        $this->unsubscribeTopic($deviceTokens, $key);

        NotificationUser::create([
            'store_id' => $this->store_id  ?? null,
            "content" => $this->content,
            "title" => $this->title,
            "type" =>  $this->type,
            "unread" => true,
            'references_value' => $this->references_value,
            'branch_id' =>  $this->branch_id,
        ]);
    }

    public function sendNotification($data, $topicName = null)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $data = [
            'to' => '/topics/' . $topicName,
            'notification' => [
                'body' => $data['body'] ?? 'Something',
                'title' => $data['title'] ?? 'Something',
                'image' => $data['image'] ?? null,
                'sound' => 'saha',
                'priority' => 'high',
                'android_channel_id' => 'noti_push_app_1',
                "content_available" => true,
            ],
            "webpush" =>  [
                "headers" => [
                    "Urgency" => "high"
                ]
            ],
            "android" =>  [
                "priority" => "high"
            ],
            "priority" =>  'high',
            "sound" => "alarm",
            'data' => [
                'branch_id' => $data['branch_id'] ?? null,
                'references_value' => $data['references_value'] ?? null,
                'title' => $data['title'] ?? null,
                'type' => $data['type'] ?? null,
                'url' => $data['url'] ?? null,
                'redirect_to' => $data['redirect_to'] ?? null,
                'type' => $data['type'] ?? null,
                "sound" => "alarm",
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                'badge' => $data['badge'] ? (int)$data['badge'] : null,
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        // 'mutable-content' => 1,
                        // 'sound' => 'saha',
                        "content-available" => 1,
                        'badge' => $data['badge'] ? (int)$data['badge'] : 0,
                    ],
                ],
                'fcm_options' => [
                    'image' => $data['image'] ?? null,
                ],
            ],
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */
    public function subscribeTopic($deviceTokens, $topicName = null)
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */
    public function unsubscribeTopic($deviceTokens, $topicName = null)
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchRemove';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $url
     * @param array $dataPost
     * @param string $method
     * @return bool
     * @throws GuzzleException
     */
    private function execute($url, $dataPost = [], $method = 'POST')
    {
        $result = false;
        try {
            $client = new Client();
            $result = $client->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=' . "AAAAZp-ZdHc:APA91bHwWGdX8i3rMW6D7QEQoVnQkhEqKbt2P7nj_bT5v3MV6y2aDjH-ozEUBm7nDMck1i9_1NwzVkIJ0GICAVWKCwStKVmjBSDbuQCrF3tK7OyTnpa49D9qRXHIkHbWE27IuxWD0dAb",
                ],
                'json' => $dataPost,
                'timeout' => 300,
            ]);

            $result = $result->getStatusCode() == Response::HTTP_OK;
        } catch (Exception $e) {
            Log::debug($e);
        }

        return $result;
    }
}
