<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\OtpConfig;
use App\Models\OtpUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtpUnitController extends Controller
{
    /**
     * Danh cách tất cả đơn vị gửi otp
     */
    public function index(Request $request)
    {
        $otp_configs = OtpConfig::where('store_id', $request->store->id)->first();
        $otp_units_custom = config('saha.otp_unit.otp_unit_custom', []);
        $list_otp_units = [];

        foreach ($otp_units_custom as $otp_unit_custom) {
            $otp_units =  OtpUnit::where('store_id', $request->store->id)
                ->where('partner', $otp_unit_custom['partner'])
                ->first();

            if ($otp_units) {

                array_push($list_otp_units, $otp_units->toArray());
            } else {

                $new_otp_unit = OtpUnit::create([
                    'store_id' => $request->store->id,
                    'sender' => $otp_unit_custom['sender'],
                    'token' => $otp_unit_custom['token'],
                    'content' => $otp_unit_custom['content'],
                    'image_url' => $otp_unit_custom['image_url'],
                    'partner' => $otp_unit_custom['partner'],
                    'is_default' => $otp_unit_custom['is_default'],
                    'is_use' => $otp_unit_custom['is_use'],
                ]);
                array_push($list_otp_units, $new_otp_unit->toArray());
            }
        }

        if (empty($otp_configs)) {
            $otp_configs = OtpConfig::create([
                'is_use' => true,
                'is_use_from_default' => true,
                'is_use_from_units' => true,
                'store_id' => $request->store->id,
            ]);
        }



        $is_use_valid = filter_var($otp_configs->is_use, FILTER_VALIDATE_BOOLEAN);
        $is_use_from_units_valid = filter_var($otp_configs->is_use_from_units, FILTER_VALIDATE_BOOLEAN);
        $data_default = $is_use_valid ? config('saha.otp_unit.otp_unit_default') : [];
        $otp_units_datas = $is_use_valid === true && $is_use_from_units_valid  === true ? $list_otp_units : [];
        $otp_units_datas_merge = array_merge($data_default, $otp_units_datas);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $otp_units_datas_merge,
        ], 200);
    }

    /**
     * Thông tin chi tiết 1 đơn vị gửi otp
     */
    public function show(Request $request)
    {
        $id = $request->route()->parameter('id');

        $otp_unit_exist = OtpUnit::where('store_id', $request->store->id)
            ->find($id);

        if (empty($otp_unit_exist)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST[0],
                'msg' => MsgCode::DOES_NOT_EXIST[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $otp_unit_exist,
        ], 200);
    }

    /**
     * Thêm đơn vị gửi otp
     * @bodyParam sender string đơn vị sử dụng otp
     * @bodyParam token string token sử dụng otp
     * @bodyParam content string nội dung sử dụng otp
     * @bodyParam image_url string ảnh của đơn vị sử dụng otp
     * @bodyParam is_default boolean đơn vị mặc định sử dụng otp
     * @bodyParam is_use boolean tắt bật đơn vị sử dụng otp
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'sender' => "required",
    //         'token' => "required",
    //         'content' => "required",
    //     ], [
    //         '*.required' => json_encode(MsgCode::DATA_TYPE_OTP_UNIT_INVALID),
    //     ]);

    //     if ($validator->fails()) {
    //         $error = json_decode($validator->getMessageBag()->all()[0]);

    //         return response()->json([
    //             'code' => 402,
    //             'success' => false,
    //             'msg_code' => $error[0],
    //             'msg' => $error[1],
    //         ], 402);
    //     }

    //     $new_otp_unit = OtpUnit::create(Helper::sahaRemoveItemArrayIfNullValue([
    //         'store_id' => $request->store->id,
    //         'sender' => $request->sender,
    //         'token' => $request->token,
    //         'content' => $request->content,
    //         'image_url' => $request->image_url,
    //         'is_default' => false,
    //         'is_use' => false,
    //     ]));

    //     return response()->json([
    //         'code' => 200,
    //         'success' => true,
    //         'msg_code' => MsgCode::SUCCESS[0],
    //         'msg' => MsgCode::SUCCESS[1],
    //         'data' => $new_otp_unit,
    //     ], 200);
    // }

    /**
     * Cập nhập đơn vị gửi otp
     * @bodyParam sender string đơn vị sử dụng otp
     * @bodyParam token string token sử dụng otp
     * @bodyParam content string nội dung sử dụng otp
     * @bodyParam image_url string ảnh của đơn vị sử dụng otp
     * @bodyParam is_default boolean đơn vị mặc định sử dụng otp
     * @bodyParam is_use boolean tắt bật đơn vị sử dụng otp
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameter('id');

        $otp_unit_exist = OtpUnit::where('store_id', $request->store->id)
            ->find($id);

        if (empty($otp_unit_exist)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST_OTP_UNIT[0],
                'msg' => MsgCode::DOES_NOT_EXIST_OTP_UNIT[1],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sender' => "required",
            'token' => "required",
            'content' => "required",
        ], [
            '*.required' => json_encode(MsgCode::DATA_TYPE_OTP_UNIT_INVALID),
        ]);

        if ($validator->fails()) {
            $error = json_decode($validator->getMessageBag()->all()[0]);

            return response()->json([
                'code' => 402,
                'success' => false,
                'msg_code' => $error[0],
                'msg' => $error[1],
            ], 402);
        }

        $otp_unit_exist->update(Helper::sahaRemoveItemArrayIfNullValue([
            'sender' => $request->sender ? $request->sender : $otp_unit_exist->sender,
            'token' => $request->token ? $request->token : $otp_unit_exist->token,
            'content' => $request->content ? $request->content : $otp_unit_exist->content,
            'image_url' => $request->image_url,
            'is_order' => $request->is_order,
            'content_order' => $request->content_order,
        ]));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $otp_unit_exist,
        ], 200);
    }

    /**
     * Cập nhập  trạng thái đơn vị gửi otp
     * @bodyParam is_use boolean tắt bật đơn vị sử dụng otp
     */
    public function updateStatus(Request $request)
    {
        $id = $request->route()->parameter('id');

        $otp_unit_exist = OtpUnit::where('store_id', $request->store->id)
            ->find($id);

        if (empty($otp_unit_exist)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::DOES_NOT_EXIST_OTP_UNIT[0],
                'msg' => MsgCode::DOES_NOT_EXIST_OTP_UNIT[1],
            ], 404);
        }

        $is_otp_unit_use = filter_var($request->is_use, FILTER_VALIDATE_BOOLEAN);

        if ($is_otp_unit_use) {

            $otp_configs = OtpConfig::where('store_id', $request->store->id)->first();

            if (empty($otp_configs)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::DOES_NOT_EXIST_OTP_CONFIG[0],
                    'msg' => MsgCode::DOES_NOT_EXIST_OTP_CONFIG[1],
                ], 404);
            }

            $otp_configs->update([
                'is_use_from_default' => false,
            ]);
        }

        $otp_unit_exist->update([
            'is_use' => $is_otp_unit_use,
        ]);
        OtpUnit::where('store_id', $request->store->id)
            ->where("id", "!=", $id)
            ->update([
                'is_use' => false,
            ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $otp_unit_exist,
        ], 200);
    }


    /**
     * Xóa đơn vị gửi otp
     * @bodyParam otp_unit_ids array mảng đơn vị sử dụng otp
     */
    // public function destroy(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'otp_unit_ids' => "required|array",
    //     ], [
    //         'otp_unit_ids.required' => json_encode(MsgCode::DATA_TYPE_INVALID),
    //         'otp_unit_ids.array' => json_encode(MsgCode::DATA_TYPE_INVALID),
    //     ]);

    //     if ($validator->fails()) {
    //         $error = json_decode($validator->getMessageBag()->all()[0]);

    //         return response()->json([
    //             'code' => 402,
    //             'success' => false,
    //             'msg_code' => $error[0],
    //             'msg' => $error[1],
    //         ], 402);
    //     }

    //     OtpUnit::where('store_id', $request->store->id)
    //         ->where('is_default', false)
    //         ->whereIn('id', $request->otp_unit_ids)
    //         ->delete();

    //     return response()->json([
    //         'code' => 200,
    //         'success' => true,
    //         'msg_code' => MsgCode::SUCCESS[0],
    //         'msg' => MsgCode::SUCCESS[1],
    //     ], 200);
    // }
}
