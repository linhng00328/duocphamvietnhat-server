<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Connect;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\EcommercePlatform;
use App\Models\MsgCode;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

/**
 * @group  User/Kết nối sàn
 */
class TiktokController extends Controller
{
    private $appKey = "681cq4dsvejuo";
    private $appSecret = "6167543cfa48742143e0cd18dd07788eadebea8d";
    private $serviceId = "7211027673199773446";

    public function generateSignTikTok($path, $options)
    {
        ksort($options);

        $signString = $this->appSecret . $path;

        foreach ($options as $keyItemOption => $itemOption) {
            if (strtolower(trim($keyItemOption)) != "sign" && strtolower(trim($keyItemOption)) != "access_token") {
                $signString .= $keyItemOption . $itemOption;
            }
        }
        $signString .= $this->appSecret;

        return hash_hmac("sha256", $signString, $this->appSecret);
    }

    /**
     * Kết nối sàn tiki
     * 
     * @queryParam 
     * 
     */
    public function connect_tiktok(Request $request)
    {
        $store_code = request('store_code');
        setcookie('IKITECHCOM', "IKITECHCOM-" . $store_code, time() + 900, "/");

        $urlConnectService = "https://services.tiktokshop.com/open/authorize?service_id=" . $this->serviceId;

        $redirect_uri = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $redirect_uri = strtok($redirect_uri, '?');

        // If we don't have an authorization code then get one
        if (!isset($_GET['code']) && !isset($_COOKIE['IKITECHCOM'])) {
            $store = Store::where('store_code', $store_code)->first();
            if ($store == null && empty($_GET['state'])) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    // 'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                    'msg_code' => "2",
                    'msg' => MsgCode::NO_STORE_EXISTS[1],
                ], 400);
            }
            header('Location: ' . $urlConnectService);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } else {
            try {
                $headers = [
                    'Cache-Control' => 'no-cache',
                    'Accept' => '*/*',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive',
                ];

                $client = new \GuzzleHttp\Client();
                // dd($urlConnectService, $_GET['code'], strtok($_GET['code'], '&'));
                $authCodeNotStore = strtok($_GET['code'], '&');

                $response = $client->request('GET', 'https://auth.tiktok-shops.com/api/v2/token/get', [
                    'query' => [
                        'app_key' => $this->appKey,
                        'app_secret' => $this->appSecret,
                        'auth_code' => $authCodeNotStore,
                        'grant_type' => 'authorized_code'
                    ],
                    "headers" => $headers
                ]);

                $jsonToken = json_decode($response->getBody())->data;

                $accessToken =  $jsonToken->access_token;

                // get authorize tiktok
                $path = "/api/shop/get_authorized_shop";

                $timeStamp = Helper::msectime() / 1000;

                $options = array(
                    'app_key' => $this->appKey,
                    'timestamp' => $timeStamp,
                    // 'sign_method' => 'sha256',
                    // 'version' => '202212',
                    // 'shop_id' => "VNLCT9LL8Y",
                    'access_token' => $accessToken,
                    // 'userToken' => $user_token,
                    // 'code' => '0_116478_H0Yzx1LmBCsF2x5MOU1k7yWb33961',
                    // 'dateStart' => '2021-11-01',
                    // 'dateEnd' => '2021-11-15',
                    // 'offerId' => "OFFER ID", #You can get this from conversion report.
                    // 'limit' => 10,
                    // 'page' => 1,
                );

                $headers = [
                    'Cache-Control' => 'no-cache',
                    'Accept' => '*/*',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive',
                ];

                $client = new \GuzzleHttp\Client();

                $sign = TiktokController::generateSignTikTok($path, $options);

                $response = $client->request('GET', 'https://open-api.tiktokglobalshop.com/api/shop/get_authorized_shop', [
                    'query' => [
                        'app_key' => $this->appKey,
                        'access_token' => $accessToken,
                        'sign' => $sign,
                        'timestamp' =>  $timeStamp,
                    ],
                    "headers" => $headers
                ]);

                if (!isset(json_decode($response->getBody())->data->shop_list[0])) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::ERROR[0],
                        'msg' => MsgCode::ERROR[1],
                    ], 400);
                }

                $jsonMe = json_decode($response->getBody())->data->shop_list[0];

                $store_code =  strtolower(str_replace("IKITECHCOM-", "", $request->cookie('IKITECHCOM')));

                $store = Store::where('store_code', $store_code)->first();
                if ($store == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                        'msg' => MsgCode::NO_STORE_EXISTS[1],
                    ], 400);
                }

                $ecommercePlatformExists = EcommercePlatform::where('store_id',  $store->id)
                    ->where('shop_id', $jsonMe->shop_id)
                    ->first();

                $now = Helper::getTimeNowCarbon();
                $exp = $now->addSeconds($jsonToken->access_token_expire_in);
                $data = [
                    "store_id" =>   $store->id,
                    "platform" => "TIKTOK",
                    "shop_id" =>  $jsonMe->shop_id,
                    "shop_isd" =>  $jsonToken->open_id,
                    "shop_name" =>  $jsonMe->shop_name,
                    "type_sync_products" => 0,
                    "type_sync_inventory" =>  0,
                    "type_sync_orders" =>  0,
                    "customer_name" =>  "",
                    "customer_phone" =>  "",
                    "expiry_token" =>  $exp,
                    "token" =>  $jsonToken->access_token,
                    "refresh_token" =>  $jsonToken->refresh_token,
                    "token_type" => null,
                    "scope" =>  null,
                ];

                if ($ecommercePlatformExists == null) {

                    $ecommercePlatformExists  =    EcommercePlatform::create($data);
                } else {
                    $ecommercePlatformExists->update($data);
                }
                unset($_COOKIE['IKITECHCOM']);
                return response()->view(
                    'ecommerce/connect/success_connect',
                );
            } catch (Exception $e) {
                return response()->view(
                    'ecommerce/connect/fail_connect',
                    [
                        'error_mess' => $e->getMessage()
                    ]
                );
            }
        }
    }
}
