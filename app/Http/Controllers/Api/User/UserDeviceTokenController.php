<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\StaffDeviceToken;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;

/**
 * @group  User/Device token
 */
class UserDeviceTokenController extends Controller
{
    /**
     * Đăng ký device token
     * @bodyParam device_id string required device_id
     * @bodyParam device_type string required 0 android | 1 ios
     * @bodyParam device_token string required device_token
     */
    public function updateDeviceTokenUser(Request $request)
    {

        if ($request->staff != null) {


            $checkDeviceTokenExists = StaffDeviceToken::where(
                'device_token',
                $request->device_token
            )->where(
                'staff_id',
                $request->staff->id
            )->first();
    
            if ($checkDeviceTokenExists != null) {
    
                $checkDeviceTokenExists->update(
                    [
                        'device_id' =>  $request->device_id,
                        'device_type' => $request->device_type,
                        'active' => true,
                        'device_token' => $request->device_token,
                    ]
                );
            } else {
    
                $checkDeviceTokenExists =  StaffDeviceToken::create(
                    [
                        'staff_id' => $request->staff->id,
                        
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
            ], 200);
        }

        if ($request->device_token == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DEVICE_TOKEN[0],
                'msg' => MsgCode::NO_DEVICE_TOKEN[1],
            ], 404);
        }

        $checkDeviceTokenExists = null;

        // UserDeviceToken::where(
        //     'user_id',
        //     $request->user->id
        // )->where('device_token', '!=', $request->device_token)->delete();

        $checkDeviceTokenExists = UserDeviceToken::where(
            'device_token',
            $request->device_token
        )->where(
            'user_id',
            $request->user->id
        )->first();

        if ($checkDeviceTokenExists != null) {

            $checkDeviceTokenExists->update(
                [
                    'device_id' =>  $request->device_id,
                    'device_type' => $request->device_type,
                    'active' => true,
                    'device_token' => $request->device_token,
                ]
            );
        } else {

            $checkDeviceTokenExists =  UserDeviceToken::create(
                [
                    'user_id' => $request->user->id,
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
            'data' => UserDeviceToken::where(
                'id',
                $checkDeviceTokenExists->id
            )->first()
        ], 200);
    }
}
