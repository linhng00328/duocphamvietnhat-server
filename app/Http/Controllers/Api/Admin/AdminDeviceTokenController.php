<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\MsgCode;
use App\Models\AdminDeviceToken;
use Illuminate\Http\Request;

/**
 * @group  Admin/Device token
 */
class AdminDeviceTokenController extends Controller
{
    /**
     * Đăng ký device token
     * @bodyParam device_id string required device_id
     * @bodyParam device_type int required 0 android | 1 ios
     * @bodyParam device_token string required device_token
     */
    public function updateDeviceTokenAdmin(Request $request)
    {


        if ($request->device_token == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DEVICE_TOKEN[0],
                'msg' => MsgCode::NO_DEVICE_TOKEN[1],
            ], 404);
        }

        $checkDeviceTokenExists = null;

        $checkDeviceTokenExists = AdminDeviceToken::where(
            'device_token',
            $request->device_token
        )->where(
            'admin_id',
            $request->admin->id
        )->first();

        if ($checkDeviceTokenExists != null) {

            $checkDeviceTokenExists->update(
                [
                    'device_id' =>  $request->device_id,
                    'device_type' => $request->device_type,
                    'active' => true
                ]
            );
        } else {

            $checkDeviceTokenExists =  AdminDeviceToken::create(
                [
                    'admin_id' => $request->admin->id,
                    'device_id' =>  $request->device_id,
                    'device_type' => $request->device_type,
                    'device_token' => $request->device_token,
                    'active' => true
                ]
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AdminDeviceToken::where(
                'id',
                $checkDeviceTokenExists->id
            )->first()
        ], 200);
    }
}
