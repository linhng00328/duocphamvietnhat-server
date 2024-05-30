<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\SaveOperationHistoryUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PublicApiSession;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/**
 * @group  User/Public API
 */
class PubicApiController extends Controller
{


    /**
     * Cập nhật cấu hình api
     * @urlParam  store_code required Store code
     * 
     * @bodyParam enable boolean required Bật hay không
     * 
     */
    public function updateConfig(Request $request)
    {

        $publicApiSession = PublicApiSession::where(
            'store_id',
            $request->store->id
        )->first();



        $data = [
            "store_id" => $request->store->id,
            "enable" => $request->enable,
            "enable_webhook" => $request->enable_webhook,
            "webhook_url" => $request->webhook_url,
        ];

        $publicApiSession->update(
            $data
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => PublicApiSession::where(
                'store_id',
                $request->store->id
            )->first(),
        ], 200);
    }

    /**
     * Lấy cấu hình token api
     * 
     * 
     * 

     */
    public function getConfig(Request $request)
    {

        $publicApiSession = PublicApiSession::where(
            'store_id',
            $request->store->id
        )->first();

        $data   = [
            "store_id" => $request->store->id,
            'enable' => true,
            'token' => Str::random(40),
            'refresh_token' => Str::random(40),
            'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
            'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
            "enable_webhook" => false,
            "webhook_url" => null,
        ];

        if ($publicApiSession  == null) {
            $publicApiSession = PublicApiSession::create(
                $data
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => PublicApiSession::where(
                'store_id',
                $request->store->id
            )->first(),
        ], 200);
    }

    /**
     * Thay đổi token
     * @urlParam  store_code required Store code
     */
    public function changeToken(Request $request)
    {

        $publicApiSession = PublicApiSession::where(
            'store_id',
            $request->store->id
        )->first();

        $data   = [
            "store_id" => $request->store->id,
            'token' => Str::random(40),
            'refresh_token' => Str::random(40),
            'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
            'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
        ];

        if ($publicApiSession  == null) {
            $publicApiSession = PublicApiSession::create(
                $data
            );
        } else {
            $publicApiSession->update(
                $data
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => PublicApiSession::where(
                'store_id',
                $request->store->id
            )->first(),
        ], 200);
    }

    /**
     * Test send webhook
     * @urlParam  store_code required Store code
     */
    public function testSendWebHook(Request $request)
    {

        SendToWebHookUtils::sendToWebHook($request,SendToWebHookUtils::NEW_CUSTOMER, ["fff"=>"dsfdfdsf"]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
