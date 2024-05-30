<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Connect;

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
class TikiController extends Controller
{

    /**
     * Kết nối sàn tiki
     * 
     * @queryParam 
     * 
     */

    public function connect_tiki(Request $request)
    {

        $store_code = request('store_code');
        $store = Store::where('store_code', $store_code)->first();
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

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => '4205245828032231',    // The client ID assigned to you by the provider
            'clientSecret'            => 'JOrSHp_HOw_VQd5Kja3l9cabESBqGKCZ',    // The client password assigned to you by the provider
            'redirectUri'             =>  $redirect_uri,
            'urlAuthorize'            => 'https://api.tiki.vn/sc/oauth2/auth',
            'urlAccessToken'          => 'https://api.tiki.vn/sc/oauth2/token',
            'urlResourceOwnerDetails' => 'https://api.tiki.vn/sc/oauth2/token',
            'verify' => true,
            'scopes' => ['offline,all']
        ]);

        $options = [
            'state' => 'IKITECHCOM-' . $store_code,
            'scope' => ['offline all']
        ];

        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {
            $authorizationUrl = $provider->getAuthorizationUrl($options);
            header('Location: ' . $authorizationUrl);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

            exit('Invalid state');
        } else {

            try {

                $headers = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic NDIwNTI0NTgyODAzMjIzMTpKT3JTSHBfSE93X1ZRZDVLamEzbDljYWJFU0JxR0tDWg==',
                ];

                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', 'https://api.tiki.vn/sc/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $_GET['code'],
                        'redirect_uri' =>  $redirect_uri,
                    ],
                    "headers" => $headers
                ]);

                $jsonToken = (json_decode($response->getBody()));

                $accessToken =  $jsonToken->access_token;

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.tiki.vn/integration/v2/sellers/me',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer $accessToken",
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                $jsonMe = (json_decode($response));



                // {
                //     "access_token": "7pIaVXUhzUDN_ImjF9koHnzuAIYk7DFauVjLr0qIxtI.v7Dw2Vyv5672wJwC-iyFKHxROHSUUQbIZYLbjdUfKxU",
                //     "expires_in": 1295999,
                //     "refresh_token": "kguKlo4NHGRRpeQaAxIx0-9wkPYh9YvfJ9p8_7HRuNk.wYMnvBRk_-CrnWVokdKFkAH9B-q3OHcgCgKiwyTn2qQ",
                //     "scope": "all offline",
                //     "token_type": "bearer"
                // }

                // {#1178 ▼
                //     +"id": 328784
                //     +"sid": "2DB7528064112E985AAA198C269FA22A0262192F"
                //     +"name": "ikitech123"
                //     +"active": 0
                //     +"logo": null
                //     +"operation_models": array:2 [▼
                //       0 => "dropship"
                //       1 => "instock"
                //     ]
                //     +"can_update_product": 1
                //     +"registration_status": "draft"
                //     +"live_at": null
                //   }


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
                    ->where('shop_id', $jsonMe->id)
                    ->first();


                $now = Helper::getTimeNowCarbon();
                $exp = $now->addSeconds($jsonToken->expires_in);
                $data = [
                    "store_id" =>   $store->id,
                    "platform" => "TIKI",
                    "shop_id" =>  $jsonMe->id,
                    "shop_isd" =>  $jsonMe->sid,
                    "shop_name" =>  $jsonMe->name,
                    "type_sync_products" => 0,
                    "type_sync_inventory" =>  0,
                    "type_sync_orders" =>  0,
                    "customer_name" =>  "",
                    "customer_phone" =>  "",
                    "expiry_token" =>  $exp,
                    "token" =>  $jsonToken->access_token,
                    "refresh_token" =>  $jsonToken->refresh_token,
                    "token_type" =>  $jsonToken->token_type,
                    "scope" =>  $jsonToken->scope,
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
