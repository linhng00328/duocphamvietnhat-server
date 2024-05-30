<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\OtpConfig;
use App\Models\OtpUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtpConfigController extends Controller
{
    /**
     * Cài đặt gửi otp
     * @bodyParam is_use boolean tắt bật sử dụng otp
     */
    public function index(Request $request)
    {
        $otp_configs = OtpConfig::where('store_id', $request->store->id)->first();

        if (empty($otp_configs)) {

            $new_otp_configs = OtpConfig::create([
                'is_use' => true,
                'is_use_from_default' => true,
                'is_use_from_units' => true,
                'store_id' => $request->store->id,
            ]);

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $new_otp_configs,
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $otp_configs,
        ], 200);
    }

    /**
     * Thay đổi cài đặt gửi otp
     * @bodyParam is_use boolean tắt bật sử dụng otp
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameter('id');
        $otp_configs_exist = OtpConfig::where('store_id', $request->store->id)
            ->find($id);

        if (empty($otp_configs_exist)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST_OTP_CONFIG[0],
                'msg' => MsgCode::DOES_NOT_EXIST_OTP_CONFIG[1],
            ], 404);
        }

        if (!empty($request->is_use_from_default) && $request->is_use_from_default) {

            OtpUnit::where('store_id', $request->store->id)
                ->update([
                    'is_use' => false,
                ]);
        }

        $is_use_valid = filter_var($request->is_use, FILTER_VALIDATE_BOOLEAN);
        $is_use_from_default_valid = filter_var($request->is_use_from_default, FILTER_VALIDATE_BOOLEAN);

        $otp_configs_exist->update(Helper::sahaRemoveItemArrayIfNullValue([
            'is_use' => $is_use_valid,
            'is_use_from_default' =>  $is_use_from_default_valid,
            'is_use_from_units' => true,
        ]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $otp_configs_exist,
        ], 200);
    }
}
