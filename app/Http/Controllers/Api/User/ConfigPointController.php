<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PointSetting;
use Illuminate\Http\Request;

/**
 * @group  User/Cấu hình điểm thưởng
 */
class ConfigPointController extends Controller
{
    const DEFAULT_CONFIG_POINTS = [
        "point_review" => 0,
        "point_introduce_customer" => 2000,
        "percent_refund" => 10,
        "order_max_point" => 0,
        "is_set_order_max_point" => false,
        "allow_use_point_order" => false,
        "money_a_point" => 1,
        "is_percent_use_max_point" => false,
        "percent_use_max_point" => true,
        "bonus_point_product_to_agency" => false,
        "bonus_point_bonus_product_to_agency" => false,
    ];

    /**
     * Cập nhật cấu hình điểm
     * @urlParam  store_code required Store code
     * @bodyParam point_review int required Điểm được khi đánh giá
     * @bodyParam point_introduce_customer int required Điểm được khi giới thiệu được 1 khách hàng
     * @bodyParam point_register_customer int required Điểm khi đăng đăng nhập đăng ký lần đâu
     * @bodyParam percent_refund double required Phần trăm hoàn xu (0-100)
     * @bodyParam order_max_point int required Số điểm tối đa khi mua hàng
     * @bodyParam is_set_order_max_point boolean required Có set tối đa điểm thưởng mua hàng ko
     * @bodyParam percent_use_max_point int required Phần trăm tối đa điểm có thể sử dụng khi mua mỗi đơn hàng
     * @bodyParam is_percent_use_max_point boolean required Có set tối đa điểm có thể sử dụng khi mua hàng
     * @bodyParam money_a_point double required Số tiền 1 point
     * @bodyParam allow_use_point_order boolean required Cho phép sử dụng điểm tại order
     * @bodyParam bonus_point_product_to_agency boolean cho phép thưởng xu khi đại lý mua hàng ko
     * @bodyParam bonus_point_bonus_product_to_agency boolean cho phép cộng xu từ sp thưởng ko
     * 
     */
    public function updateConfig(Request $request)
    {

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();



        $data = [
            "store_id" => $request->store->id,
            "point_review" => $request->point_review,
            "point_introduce_customer" => $request->point_introduce_customer,
            "point_register_customer" => $request->point_register_customer,
            "percent_refund" => $request->percent_refund,
            "order_max_point" => $request->order_max_point,
            "is_set_order_max_point" => $request->is_set_order_max_point,
            "allow_use_point_order" => $request->allow_use_point_order,
            "money_a_point" => $request->money_a_point,
            "percent_use_max_point" => $request->percent_use_max_point,
            "is_set_order_max_point" => $request->is_set_order_max_point,
            "is_percent_use_max_point" => $request->is_percent_use_max_point,
            "bonus_point_product_to_agency" => $request->bonus_point_product_to_agency,
            "bonus_point_bonus_product_to_agency" => $request->bonus_point_bonus_product_to_agency,
        ];

        if ($pointSetting  == null) {
            $pointSetting = PointSetting::create(
                $data
            );
        } else {
            $pointSetting->update(
                $data
            );
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_POINT,
            "Cập nhật cấu hình xu ",
            $pointSetting ->id,
           null
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => PointSetting::where(
                'store_id',
                $request->store->id
            )->first(),
        ], 200);
    }

    /**
     * Lấy cấu hình điểm
     * point_review int required Điểm được khi đánh giá \n
     * 
     * point_introduce_customer int required Điểm được khi giới thiệu được 1 khách hàng
     * 
     * percent_refund double required Phần trăm hoàn xu (0-100)
     * 
     * order_max_point int required Số điểm tối đa khi mua hàng
     * 
     * is_set_order_max_point boolean required Có set tối đa điểm mua hàng ko
     * 
     * money_a_point double required Số tiền 1 point
     * 
     * allow_use_point_order boolean required Cho phép sử dụng điểm tại order
     * 
     * percent_use_max_point Phần trăm tối đa điểm có thể sử dụng khi mua mỗi đơn hàng
     * 
     * is_set_order_max_point Có set tối đa điểm có thể sử dụng khi mua hàng
     * 
     * 

     */
    public function getConfig(Request $request)
    {

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        $data = ConfigPointController::DEFAULT_CONFIG_POINTS;

        $data["store_id"] = $request->store->id;

        if ($pointSetting  == null) {
            $pointSetting = PointSetting::create(
                $data
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => PointSetting::where(
                'store_id',
                $request->store->id
            )->first(),
        ], 200);
    }

    /**
     * Khôi phục mặc định
     * @urlParam  store_code required Store code
     */
    public function reset(Request $request)
    {

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();


        $data = ConfigPointController::DEFAULT_CONFIG_POINTS;

        $data["store_id"] = $request->store->id;

        if ($pointSetting  == null) {
            $pointSetting = PointSetting::create(
                $data
            );
        } else {
            $pointSetting->update(
                $data
            );
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => PointSetting::where(
                'store_id',
                $request->store->id
            )->first(),
        ], 200);
    }
}
