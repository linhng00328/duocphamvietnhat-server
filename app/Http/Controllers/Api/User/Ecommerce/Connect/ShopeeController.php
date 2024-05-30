<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Connect;

use App\Helper\Ecommerce\EcommerceUtils;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\EcommercePlatform;
use App\Models\MsgCode;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * @group  User/Kết nối sàn
 */
class ShopeeController extends Controller
{

    static function authShop($timestamp)
    {
        $path = "/api/v2/shop/auth_partner";
        $redirectUrl = "https://dev.doapp.vn/api/store/ecommerce/connect/shopee?state=IKITECHCOM-chinhbv";

        $sign = self::generateSign($path, $timestamp);
        return sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s&redirect=%s", EcommerceUtils::SHOPEE_HOST, $path, EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $timestamp, $sign, $redirectUrl);
    }


    /**
     * Kết nối sàn shopee
     * 
     * @queryParam 
     * 
     */

    public function connect_shopee(Request $request)
    {
        $store_code = request('store_code');
        $store = Store::where('store_code', $store_code)->first();
        $timestamp = time();

        $path = "/api/v2/shop/auth_partner";
        $redirectUrl = "https://dev.doapp.vn/api/store/ecommerce/connect/shopee?state=IKITECHCOM-chinhbv";

        $sign = self::generateSign($path, $timestamp);
        $urlConnectService = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s&redirect=%s", EcommerceUtils::SHOPEE_HOST, $path, EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $timestamp, $sign, $redirectUrl);

        if ($store == null && empty($_GET['state'])) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 400);
        }

        $redirect_uri = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $redirect_uri = strtok($redirect_uri, '?');

        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {
            header('Location: ' . $urlConnectService);
            exit;
        } else {
            try {
                $shop_id = $_GET['shop_id'];
                $jsonToken = self::getTokenShopLevel($_GET['code'], $shop_id, $timestamp);

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

                $jsonMe = self::getShopInfo($jsonToken->access_token, $shop_id, $timestamp);
                $ecommercePlatformExists = EcommercePlatform::where('store_id', $store->id)
                    ->where('shop_id', $shop_id)
                    ->first();

                $now = Helper::getTimeNowCarbon();
                $exp = $now->addSeconds($jsonToken->expire_in);
                $data = [
                    "store_id" =>   $store->id,
                    "platform" => "SHOPEE",
                    "shop_id" =>  $shop_id,
                    "shop_isd" =>  "",
                    "shop_name" =>  $jsonMe->shop_name,
                    "type_sync_products" => false,
                    "type_sync_inventory" =>  false,
                    "type_sync_orders" =>  false,
                    "customer_name" =>  "",
                    "customer_phone" =>  "",
                    "expiry_token" =>  $exp,
                    "token" =>  $jsonToken->access_token,
                    "refresh_token" =>  $jsonToken->refresh_token,
                    "token_type" => null,
                    "scope" => null,
                ];

                if ($ecommercePlatformExists == null) {

                    $ecommercePlatformExists = EcommercePlatform::create($data);
                } else {
                    $ecommercePlatformExists->update($data);
                }

                return response()->view(
                    'ecommerce/connect/success_connect'
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

    static function getTokenShopLevel($code, $shopId, $timestamp)
    {
        $path = "/api/v2/auth/token/get";
        $body = array("code" => $code, "shop_id" => (int)$shopId, "partner_id" => (int)EcommerceUtils::SHOPEE_LIVE_PARTER_ID);
        $sign = self::generateSign($path, $timestamp);
        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", EcommerceUtils::SHOPEE_HOST, $path, EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $timestamp, $sign);

        $c = curl_init($url);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($c);

        $response = json_decode($resp);
        return $response;
    }

    static public function refresh_token($ecommercePlatform)
    {
        $timestamp = time();
        $now = Helper::getTimeNowCarbon();
        $path = "/api/v2/auth/access_token/get";

        if ($ecommercePlatform->platform == "SHOPEE") {

            $bodyRefreshToken = array(
                "shop_id" => (int)$ecommercePlatform->shop_id,
                "refresh_token" => $ecommercePlatform->refresh_token,
                "partner_id" => (int)EcommerceUtils::SHOPEE_LIVE_PARTER_ID
            );

            $sign = self::generateSign($path, $timestamp);
            $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", EcommerceUtils::SHOPEE_HOST, $path, EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $timestamp, $sign);

            $c = curl_init($url);
            curl_setopt($c, CURLOPT_POST, 1);
            curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($bodyRefreshToken));
            curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            $resp = curl_exec($c);

            $jsonRefreshToken = json_decode($resp);

            $expireIn = $now->addSeconds($jsonRefreshToken->expire_in);

            $data = [
                "expiry_token" =>  $expireIn,
                "token" =>  $jsonRefreshToken->access_token,
                "refresh_token" =>  $jsonRefreshToken->refresh_token,
            ];

            if ($ecommercePlatform == null) {
                $ecommercePlatform = EcommercePlatform::create($data);
            } else {
                $ecommercePlatform->update($data);
            }
        }
    }

    static public function getShopInfo($access_token, $shop_id, $timestamp)
    {
        $curl = curl_init();

        $baseString = sprintf("%s%s%s%s%s", EcommerceUtils::SHOPEE_LIVE_PARTER_ID, "/api/v2/shop/get_shop_info", $timestamp, $access_token, $shop_id);
        $sign = hash_hmac('sha256', $baseString, EcommerceUtils::SHOPEE_LIVE_KEY);
        $url = 'https://partner.shopeemobile.com/api/v2/shop/get_shop_info?access_token=' . $access_token . '&partner_id=2005733&shop_id=' . $shop_id . '&sign=' . $sign . '&timestamp=' . $timestamp;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        return json_decode($response);
    }

    static public function generateSign($path, $timestamp, $token = null, $shop_id = null)
    {
        $baseString = sprintf("%s%s%s", EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $path, $timestamp);

        if ($token != null && $shop_id != null) {
            $baseString = sprintf("%s%s%s%s%s", EcommerceUtils::SHOPEE_LIVE_PARTER_ID, $path, $timestamp, $token, $shop_id);
        }

        return hash_hmac('sha256', $baseString, EcommerceUtils::SHOPEE_LIVE_KEY);
    }
}
