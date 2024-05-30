<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Connect;

use App\Helper\Ecommerce\EcommerceUtils;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\EcommercePlatform;
use App\Models\MsgCode;
use App\Models\Store;
use Exception;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Lazada\LazopClient;
use Lazada\LazopRequest;
use Lazada\UrlConstants;

/**
 * @group  User/Kết nối sàn
 */
class LazadaController extends Controller
{

    static public function refresh_token($store_id, $shop_id, $refresh_token)
    {
        $c = new LazopClient(UrlConstants::$api_authorization_url, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
        $requestLazop = new LazopRequest('/auth/token/refresh');
        $requestLazop->addApiParam('refresh_token', $refresh_token);
        $jsonToken = json_decode($c->execute($requestLazop));

        $ecommercePlatformExists = EcommercePlatform::where('store_id',  $store_id)
            ->where('shop_id', $shop_id)
            ->first();

        $now = Helper::getTimeNowCarbon();

        $expireIn = $now->addSeconds($jsonToken->expires_in);

        $data = [
            "expiry_token" =>  $expireIn,
            "token" =>  $jsonToken->access_token,
            "refresh_token" =>  $jsonToken->refresh_token,
        ];

        if ($ecommercePlatformExists == null) {
            $ecommercePlatformExists = EcommercePlatform::create($data);
        } else {
            $ecommercePlatformExists->update($data);
        }
    }

    /**
     * Kết nối sàn lazada
     * 
     * @queryParam 
     * 
     */
    public function connect_lazada(Request $request)
    {
        $lazadaUrl = "https://auth.lazada.com/rest";

        $redirect_uri = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $redirect_uri = strtok($redirect_uri, '?');

        $url = "https://auth.lazada.com/oauth/authorize?response_type=code&url=" . $redirect_uri . "&client_id=" . EcommerceUtils::LAZADA_API_KEY . "&state=IKITECHCOM-" . request('store_code');

        $store_code = request('store_code');
        $store = Store::where('store_code', $store_code)->first();

        if ($store == null && empty($_GET['state']) && empty($_GET['code'])) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 400);
        }

        // dd(Helper::generateSign())

        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {

            header('Location: ' . $url);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } else {
            try {
                $c = new LazopClient($lazadaUrl, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
                $request = new LazopRequest('/auth/token/create');
                $request->addApiParam('code', $_GET['code']);
                $response = $c->execute($request);

                $jsonToken = (json_decode($response));

                $accessToken =  $jsonToken->access_token;

                $c = new LazopClient(UrlConstants::$api_gateway_url_vn, EcommerceUtils::LAZADA_API_KEY, EcommerceUtils::LAZADA_API_SECRET);
                $request = new LazopRequest('/seller/get', 'GET');
                $jsonMe = json_decode($c->execute($request, $accessToken))->data;

                $store_code =  str_replace("IKITECHCOM-", "", $_GET['state']);

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
                    ->where('shop_id', $jsonMe->seller_id)
                    ->first();

                $now = Helper::getTimeNowCarbon();

                $exp = $now->addSeconds($jsonToken->expires_in);
                $data = [
                    "store_id" =>   $store->id,
                    "platform" => "LAZADA",
                    "shop_id" =>  $jsonMe->seller_id,
                    "shop_isd" =>  $jsonMe->short_code,
                    "shop_name" =>  $jsonMe->name,
                    "type_sync_products" => 0,
                    "type_sync_inventory" =>  0,
                    "type_sync_orders" =>  0,
                    "customer_name" =>  "",
                    "customer_phone" =>  "",
                    "expiry_token" =>  $exp,
                    "token" =>  $jsonToken->access_token,
                    "refresh_token" =>  $jsonToken->refresh_token,
                    "token_type" =>  null,
                    "scope" =>  null,
                ];

                if ($ecommercePlatformExists == null) {

                    $ecommercePlatformExists  =    EcommercePlatform::create($data);
                } else {
                    $ecommercePlatformExists->update($data);
                }

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
