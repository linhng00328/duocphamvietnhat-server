<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Models\ConfigNotification;
use App\Models\Customer;
use App\Models\CustomerDeviceToken;
use App\Models\NotificationCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PushNotificationCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store_id;
    protected $customer_id;
    protected $content;
    protected $title;
    protected $type;
    protected $references_value;
    protected $type_action;
    protected $value_action;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $store_id,
        $customer_id,
        $title,
        $content,
        $type,
        $references_value,
        $type_action = null,
        $value_action = null
    ) {


        $this->store_id = $store_id;
        $this->customer_id = $customer_id;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->references_value = $references_value;
        $this->type_action = $type_action;
        $this->value_action = $value_action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $configExis = ConfigNotification::where(
            'store_id',
            $this->store_id
        )->first();

        if ($this->customer_id  != null) {
            NotificationCustomer::create([
                'customer_id' => $this->customer_id ?? null,
                'store_id' => $this->store_id  ?? null,
                "content" => $this->content,
                "title" => $this->title,
                "type" =>  $this->type,
                'unread' => true,
                "references_value" => $this->references_value,
                "type_action" => $this->type_action,
                "value_action" => $this->value_action,
            ]);
            $customer = Customer::where(
                'store_id',
                $this->store_id
            )->where('id', $this->customer_id)->first();

            if ($customer  != null) {
                $customer->update([
                    'notifications_count' =>  $customer->notifications_count + 1
                ]);
            }
        } else {
            $listCustomer = Customer::where(
                'store_id',
                $this->store_id
            )->get();

            $save_noti_list = array();

            foreach ($listCustomer as $customer) {
                array_push(
                    $save_noti_list,
                    [
                        'customer_id' => $customer->id ?? null,
                        'store_id' => $this->store_id  ?? null,
                        "content" => $this->content,
                        "title" => $this->title,
                        "type" =>  $this->type,
                        'unread' => true,
                        "references_value" => $this->references_value,
                        "type_action" => $this->type_action,
                        "value_action" => $this->value_action,
                        "created_at" => Helper::getTimeNowString(),
                        "updated_at" => Helper::getTimeNowString(),
                    ]
                );
                $customer->update([
                    'notifications_count' =>  $customer->notifications_count + 1
                ]);
            }
            $split_save_noti_list = array_chunk($save_noti_list, 500);
            foreach ($split_save_noti_list as $parentArray) {
                NotificationCustomer::insert($parentArray); // Eloquent approach
            }
        }

        if ($configExis != null &&  $configExis->key != null) {
            $deviceTokens = [];

            if ($this->customer_id  != null) {
                $deviceTokens = CustomerDeviceToken::where(
                    'customer_id',
                    $this->customer_id
                )->where(
                    'store_id',
                    $this->store_id
                )
                    ->pluck('device_token')
                    ->toArray();
            } else {

                $deviceTokens = CustomerDeviceToken::where(
                    'store_id',
                    $this->store_id
                )
                    ->pluck('device_token')
                    ->toArray();
            }

            $data = [
                'body' => $this->content,
                'title' =>  $this->title,
                'type' => $this->type,
                'references_value' => $this->references_value,
                'type_action'  => $this->type_action,
                'value_action'  => $this->value_action,
            ];

            $deviceTokens = array_unique($deviceTokens);

            $splitArray = array_chunk($deviceTokens, 500);

            foreach ($splitArray as $parentArray) {
                $topicName = Helper::getRandomOrderString();
                $this->subscribeTopic($parentArray, $topicName);
                $this->sendNotification($data, $topicName);
                $this->unsubscribeTopic($parentArray, $topicName);
            }
        }
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
                'references_value' => $data['references_value'] ?? null,
                'type_action' => $data['type_action'] ?? null,
                'value_action' => $data['value_action'] ?? null,
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
                'url' => $data['url'] ?? null,
                'redirect_to' => $data['redirect_to'] ?? null,
                'type' => $data['type'] ?? null,
                'references_value' => $data['references_value'] ?? null,
                'type_action' => $data['type_action'] ?? null,
                'value_action' => $data['value_action'] ?? null,
                "sound" => "alarm",
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                        'sound' => 'saha',
                        'badge' => 1,
                        "content-available" => 1
                    ],
                ],
                'fcm_options' => [
                    'image' => $data['image'] ?? null,
                ],
            ],
        ];

        $this->execute($url, $data);
    }


    public function subscribeTopic($deviceTokens, $topicName = null)
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $data =  $this->execute($url, $data);

        if ($data  == null) return;
        $arr_rt = json_decode($data);


        $arr_device_token_err = array();
        if (is_array($arr_rt) && count($arr_rt) > 0) {
            $index = 0;
            foreach ($arr_rt as $item_rt) {

                if (isset($item_rt->error)) {
                    array_push($arr_device_token_err, $deviceTokens[$index]);
                }
                $index++;
            }
        }
        //// //// //// //// //// ////
        if (is_array($arr_device_token_err) && count($arr_device_token_err) > 0) {
            CustomerDeviceToken::whereIn('device_token', $arr_device_token_err)->delete();
        }
    }

    public function unsubscribeTopic($deviceTokens, $topicName = null)
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchRemove';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    private function execute($url, $dataPost = [], $method = 'POST')
    {

        $configExis = ConfigNotification::where(
            'store_id',
            $this->store_id
        )->first();

        if ($configExis == null) {
            return;
        }


        $result = false;
        try {
            $client = new Client();
            $result = $client->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=' . ($configExis->key ?? "AAAAJxv7drk:APA91bEP40veWlD6OMcb5K9dCsrLBbXYx6OVVb9TSju1WcSO1WVtAki0ZhJwfOFHsGjWMg6lpC02uRltyHq0z27ayscz2g2bSExlG9PmRUe4fLyZO1bMPjMMiOSV6JIXED9J0fS7TSCZ"),
                ],
                'json' => $dataPost,
                'timeout' => 300,
            ]);

            $result = $result->getBody()->getContents();
        } catch (Exception $e) {
            Log::debug($e);
        }

        return $result;
    }
}
