<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\CustomerDeviceToken;
use Exception;
use Illuminate\Http\Request;

/**
 * @group  Customer/Device token
 */
class CustomerDeviceTokenController extends Controller
{
    /**
     * Đăng ký device token
     * @bodyParam device_id string required device_id
     * @bodyParam device_type string required 0 android | 1 ios
     * @bodyParam device_token string required device_token
     */
    public function updateDeviceTokenCustomer(Request $request)
    {


        if ($request->device_token == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DEVICE_TOKEN[0],
                'msg' => MsgCode::NO_DEVICE_TOKEN[1],
            ], 404);
        }

        try {
            $checkDeviceTokenExists = null;

            $checkDeviceTokenExists = CustomerDeviceToken::where(
                'device_token',
                $request->device_token
            )->where(
                'store_id',
                $request->store->id
            )->first();

            if ($checkDeviceTokenExists != null) {

                $checkDeviceTokenExists->update(
                    [
                        'device_id' =>  $request->device_id,
                        'device_type' => $request->device_type,
                        'active' => true,
                        'customer_id' =>  $request->customer === null ? null : $request->customer->id,
                    ]
                );
            } else {

                $checkDeviceTokenExists =  CustomerDeviceToken::create(
                    [
                        'customer_id' =>  $request->customer === null ? null : $request->customer->id,
                        'store_id' =>  $request->store->id,
                        'device_id' =>  $request->device_id,
                        'device_type' => 1,
                        'device_token' => $request->device_token,
                        'active' => true
                    ]
                );
            }
        } catch (Exception $e) {
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
