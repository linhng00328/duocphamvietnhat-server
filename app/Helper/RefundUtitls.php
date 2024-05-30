<?php

namespace App\Helper;

use App\Models\MsgCode;
use App\Services\BalanceCustomerService;
use Illuminate\Support\Facades\Cache;

class RefundUtitls
{


    static function auto_refund_money_for_ctv($orderExists, $request)
    {

        if (Cache::lock('auto_refund_money_for_ctv' .  $orderExists->order_code, 1)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã xử lý",
            ], 400);
        }

        if (
            ($orderExists->order_status ==  StatusDefineCode::CUSTOMER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::OUT_OF_STOCK ||
                $orderExists->order_status ==  StatusDefineCode::USER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::DELIVERY_ERROR ||
                $orderExists->order_status ==  StatusDefineCode::CUSTOMER_RETURNING ||
                $orderExists->payment_status ==  StatusDefineCode::PAY_REFUNDS) 
          
        ) {
            if (  $orderExists->has_refund_money_for_ctv == false && $orderExists->balance_collaborator_used > 0 && $orderExists->customer_id == $orderExists->collaborator_by_customer_id) {
                BalanceCustomerService::change_balance_collaborator(
                    $request->store->id,
                    $orderExists->customer_id,
                    BalanceCustomerService::CTV_CANCEL_ORDER,
                    $orderExists->balance_collaborator_used,
                    $orderExists->id,
                    $orderExists->order_code,
                );
                $orderExists->update([
                    'has_refund_money_for_ctv' => true
                ]);
            }

            if (  $orderExists->has_refund_money_for_agency == false && $orderExists->balance_agency_used > 0) {
                BalanceCustomerService::change_balance_agency(
                    $request->store->id,
                    $orderExists->customer_id,
                    BalanceCustomerService::AGENCY_CANCEL_ORDER,
                    $orderExists->balance_agency_used,
                    $orderExists->id,
                    $orderExists->order_code,
                );
                $orderExists->update([
                    'has_refund_money_for_agency' => true
                ]);
            }
        }
    }

    static function auto_refund_point_for_customer($orderExists, $request)
    {
        if (Cache::lock('auto_refund_point_for_customer' .  $orderExists->order_code, 1)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã xử lý",
            ], 400);
        }


        if (
            ($orderExists->order_status ==  StatusDefineCode::CUSTOMER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::OUT_OF_STOCK ||
                $orderExists->order_status ==  StatusDefineCode::USER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::DELIVERY_ERROR ||
                $orderExists->order_status ==  StatusDefineCode::CUSTOMER_RETURNING ||
                $orderExists->payment_status ==  StatusDefineCode::PAY_REFUNDS) &&
            $orderExists->has_refund_point_for_customer == false
        ) {
            if ($orderExists->total_points_used > 0) {
                PointCustomerUtils::add_sub_point(
                    PointCustomerUtils::CUSTOMER_CANCEL_ORDER,
                    $request->store->id,
                    $orderExists->customer_id,
                    $orderExists->total_points_used,
                    $orderExists->id,
                    $orderExists->order_code
                );
            }
            $orderExists->update([
                'has_refund_point_for_customer' => true
            ]);
        }
    }
}
