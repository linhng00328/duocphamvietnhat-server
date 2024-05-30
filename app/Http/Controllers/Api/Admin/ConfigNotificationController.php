<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigNotification;
use App\Models\MsgCode;
use App\Models\Store;
use Illuminate\Http\Request;
use Exception;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @group  Admin/Cấu hình thông báo 

 */

class ConfigNotificationController extends Controller
{
    /**
     * Cấu hình thông báo
     */
    public function config(Request $request)
    {

        
        $store = Store::where('store_code', $request->store_code)->first();


        if ($store == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 400);
        }

        if ($request->key == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::KEY_IS_REQUIRED[0],
                'msg' => MsgCode::KEY_IS_REQUIRED[1],
            ], 400);
        }


        $config = config('saha.shipper.list_shipper')[0];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'key=' . $request->key,
        ];
        try {
            $response = $client->request(
                'POST',
                "https://fcm.googleapis.com/fcm/send",
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]
            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode == 401) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_KEY[0],
                    'msg' => MsgCode::INVALID_KEY[1],
                ], 400);
            }
        } catch (Exception $e) {
        }

        $configExis = ConfigNotification::where(
            'store_id',
            $store->id
        )->first();

        if ($configExis == null) {
            ConfigNotification::create([
                'store_id' => $store->id,
                'key' => $request->key
            ]);
        } else {
            $configExis->update([
                'key' => $request->key
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Thông tin cài đặt
     * 
     * @queryParam  store_code Code stop
     * 
     * 
     */
    public function getOne(Request $request)
    {

        $store_code = request('store_code');

        $store = Store::where('store_code',  $store_code)->first();


        if ($store == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 400);
        }

        $configExis = ConfigNotification::where(
            'store_id',
            $store->id
        )->first();


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $configExis,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
