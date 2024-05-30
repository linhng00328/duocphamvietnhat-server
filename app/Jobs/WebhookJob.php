<?php

namespace App\Jobs;

use GuzzleHttp\Client as GuzzleClient;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PublicApiSession;
use App\Models\HistorySendWebhook;

class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store_id;
    protected $type;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id, $type,  $data)
    {
        $this->store_id = $store_id;
        $this->type = $type;
        $this->data = $data;
    }

    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $store_id = $this->store_id;
        $type = $this->type;
        $data = $this->data;

        $publicApiSession = PublicApiSession::where(
            'store_id',
            $store_id
        )->first();

        if ($publicApiSession != null && $publicApiSession->enable_webhook == true && filter_var($publicApiSession->webhook_url, FILTER_VALIDATE_URL)) {

            $json =  [
                "type" => $type,
                "data" => $data
            ];
            $historySendWebhook = HistorySendWebhook::create([
                'store_id' =>  $store_id,
                'type' => $type,
                "webhook_url" =>  $publicApiSession->webhook_url,
                'json_data_send' => json_encode($json)
            ]);

            try {
                $client = new GuzzleClient();
                $response = $client->post(
                    $publicApiSession->webhook_url,
                    [
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'query' => [],
                        'json' => $json
                    ]
                );
                $body = (string) $response->getBody();
                $statusCode = $response->getStatusCode();
                $historySendWebhook->update([
                    'status' =>   $statusCode,
                    'json_data_success' => $body
                ]);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = (string) $e->getResponse()->getBody();

                $historySendWebhook->update([
                    'status' =>   $statusCode,
                    'json_data_fail' => $body
                ]);

                return json_decode("[]");
                return new Exception('error');
            } catch (Exception $e) {

                return json_decode("[]");
                return new Exception('error');
            }
        }
    }
}
