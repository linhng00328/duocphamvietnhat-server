<?php

namespace App\Helper;

use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\PointHistory;
use App\Models\PointSetting;
use Exception;
use Illuminate\Support\Facades\Cache;

class PointCustomerUtils
{
    // const ROLL_CALL_1_DAY = "ROLL_CALL_1_DAY";
    // const ROLL_CALL_7_DAY = "ROLL_CALL_7_DAY";
    const REVIEW_PRODUCT = "REVIEW_PRODUCT"; //ĐÁnh giá
    const ORDER_COMPLETE = "ORDER_COMPLETE"; //Hoàn thành đơn hàng
    const REFERRAL_CUSTOMER = "REFERRAL_CUSTOMER"; //Giới thiệu khách hàng
    const REGISTER_CUSTOMER = "REGISTER_CUSTOMER"; //Khách hàng đăng ký
    const USE_POINT_IN_ORDER = "USE_POINT_IN_ORDER"; //Sử dụng điểm
    const REFUND_ORDER = "REFUND_ORDER"; //Hoan thanh don hang
    const GIFT_AT_SPIN_WHEEL = "GIFT_AT_SPIN_WHEEL"; //Quà xu tại mini game vòng quay
    const CUSTOMER_CANCEL_ORDER = "CUSTOMER_CANCEL_ORDER"; //Customer hủy đơn và các trạng thái tương tự trả lại xu
    const BONUS_POINT_AGENCY = "BONUS_POINT_AGENCY"; //Customer hủy đơn và các trạng thái tương tự trả lại xu
    const SUB_POINT = "SUB_POINT"; //Trừ xu
    const ADD_POINT = "ADD_POINT"; //Cộng xu

    public static function bonus_point_for_agency_product_from_order($request, $order)
    {

        if (Cache::lock('bonus_point_for_agency_product_from_order' .  $order->order_code, 1)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã xử lý",
            ], 400);
        }

        if ($order->order_status  ==  StatusDefineCode::COMPLETED && $order->payment_status  ==  StatusDefineCode::PAID) {
            $pointSetting = PointSetting::where(
                'store_id',
                $request->store->id
            )->first();

            $point_for_agency = 0;

            //tính điểm cho customer
            if ($pointSetting != null && $pointSetting->bonus_point_product_to_agency == true) {

                foreach ($order->line_items as $lineItem) {
                    if ($pointSetting->bonus_point_product_to_agency == true) {
                        if ($lineItem->is_bonus == false) {
                            $point_for_agency = $point_for_agency + ($lineItem->product->point_for_agency * $lineItem->quantity);
                        }

                        if ($lineItem->is_bonus == true &&  $pointSetting != null && $pointSetting->bonus_point_bonus_product_to_agency == true) {
                            $point_for_agency = $point_for_agency + ($lineItem->product->point_for_agency * $lineItem->quantity);
                        }
                    }
                }

                if ($point_for_agency > 0) {
                    if ($order->agency_by_customer_id != null) {
                        $order->update([
                            'point_for_agency' =>  $point_for_agency
                        ]);

                        PointCustomerUtils::add_sub_point(
                            PointCustomerUtils::BONUS_POINT_AGENCY,
                            $request->store->id,
                            $order->agency_by_customer_id,
                            (int)($point_for_agency),
                            $order->id,
                            $order->order_code
                        );
                    }
                }
            }
        }
    }

    public static function bonus_point_from_order($request, $order)
    {

        if (Cache::lock('bonus_point_from_order' .  $order->order_code, 1)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã xử lý",
            ], 400);
        }

        if ($order->order_status  ==  StatusDefineCode::COMPLETED && $order->payment_status  ==  StatusDefineCode::PAID && $order->points_awarded_to_customer == 0) {
            $pointSetting = PointSetting::where(
                'store_id',
                $request->store->id
            )->first();


            //tính điểm cho customer
            if ($pointSetting != null) {

                //Thêm đỉm thưởng xu
                if ($order->customer_id != null && $pointSetting->money_a_point  > 0 && $pointSetting->percent_refund > 0 && $pointSetting->percent_refund <= 100) {
                    $moneyRefund = ($order->total_after_discount + $order->bonus_points_amount_used + $order->balance_collaborator_used) * ($pointSetting->percent_refund / 100);
                    $point = (int)($moneyRefund / $pointSetting->money_a_point);

                    //Kiem tra so luong xu toi da duoc tang khi mua hang
                    if ($pointSetting->is_set_order_max_point === true &&  $pointSetting->order_max_point <  $point) {
                        $point = $pointSetting->order_max_point;
                    }


                    if ($point > 0) {
                        PointCustomerUtils::add_sub_point(
                            PointCustomerUtils::ORDER_COMPLETE,
                            $request->store->id,
                            $order->customer_id,
                            (int)($point),
                            $order->id,
                            $order->order_code
                        );

                        $order->update([
                            'points_awarded_to_customer' => (int)$point
                        ]);
                    }
                }
            }
        }
    }

    public static function add_sub_point($type, $store_id, $customer_id, $point, $references_id, $references_value, $note = "")
    {
        $customer = Customer::where("id", $customer_id)->first();
        if ($customer == null) {

            return;
        }

        $current_point = ($point ?? 0) + ($customer->points ?? 0);


        if ($customer->is_passersby == true) {
            return;
        }

        try {
            if ($type == PointCustomerUtils::ADD_POINT || $type == PointCustomerUtils::SUB_POINT) {

                $history = PointHistory::where("customer_id", $customer_id)->where("type", $type)->where("references_value", $references_value)->first();
                if ($history == null) {

                    PointHistory::create([
                        "store_id" => $store_id,
                        "customer_id" => $customer_id,
                        "type" => $type,
                        "current_point" =>  $current_point,
                        "point" => $point,
                        "references_id" => $references_id,
                        "references_value" => $references_value,
                        "note" => $note
                    ]);
                    if ($customer != null) {
                        $customer->update([
                            "points" =>  $current_point ?? 0
                        ]);
                    }
                }
            }

            if ($type == PointCustomerUtils::USE_POINT_IN_ORDER) {
                $history = PointHistory::where("customer_id", $customer_id)->where("type", $type)->where("references_value", $references_value)->first();
                if ($history == null) {

                    PointHistory::create([
                        "store_id" => $store_id,
                        "customer_id" => $customer_id,
                        "type" => $type,
                        "current_point" =>  $current_point,
                        "point" => $point,
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                    if ($customer != null) {
                        $customer->update([
                            "points" =>  $current_point ?? 0
                        ]);
                    }
                }
            }

            if ($type == PointCustomerUtils::BONUS_POINT_AGENCY) {

                $pointSetting = PointSetting::where(
                    'store_id',
                    $store_id
                )->first();

                if ($pointSetting != null &&  $pointSetting->bonus_point_product_to_agency == true) {
                    PointHistory::create([
                        "store_id" => $store_id,
                        "customer_id" => $customer_id,
                        "type" => $type,
                        "current_point" =>  $current_point,
                        "point" => $point,
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);

                    if ($customer != null) {
                        $customer->update([
                            "points" =>  $current_point ?? 0
                        ]);
                    }
                }
            }

            if ($type == PointCustomerUtils::REFUND_ORDER) {

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);

                if ($customer != null) {
                    $customer->update([
                        "points" =>  $current_point ?? 0
                    ]);
                }
            }

            if ($type == PointCustomerUtils::ORDER_COMPLETE) {
                $history = PointHistory::where("customer_id", $customer_id)->where("type", $type)->where("references_value", $references_value)->first();
                if ($history == null) {
                    PointHistory::create([
                        "store_id" => $store_id,
                        "customer_id" => $customer_id,
                        "type" => $type,
                        "current_point" =>  $current_point,
                        "point" => $point,
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                    if ($customer != null && $customer->is_passersby == false) {
                        $customer->update([
                            "points" =>  $current_point ?? 0
                        ]);
                    }
                }
            }



            if ($type == PointCustomerUtils::REGISTER_CUSTOMER) {
                $history = PointHistory::where("customer_id", $customer_id)->where("type", $type)->where("references_value", $references_value)->first();
                if ($history == null) {

                    PointHistory::create([
                        "store_id" => $store_id,
                        "customer_id" => $customer_id,
                        "type" => $type,
                        "current_point" =>  $current_point,
                        "point" => $point,
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);

                    if ($customer != null) {
                        $customer->update([
                            "points" =>  $current_point ?? 0
                        ]);
                    }
                }
            }

            if ($type == PointCustomerUtils::REFERRAL_CUSTOMER) {


                $history = PointHistory::where("customer_id", $customer_id)->where("type", $type)->where("references_value", $references_value)->first();
                if ($history == null) {

                    PointHistory::create([
                        "store_id" => $store_id,
                        "customer_id" => $customer_id,
                        "type" => $type,
                        "current_point" =>  $current_point,
                        "point" => $point,
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);

                    if ($customer != null) {
                        $customer->update([
                            "points" =>  $current_point ?? 0
                        ]);
                    }
                }
            }

            if ($type == PointCustomerUtils::REVIEW_PRODUCT) {

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);

                if ($customer != null) {
                    $customer->update([
                        "points" =>  $current_point
                    ]);
                }
            }

            if ($type == PointCustomerUtils::CUSTOMER_CANCEL_ORDER) {

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);

                if ($customer != null) {
                    $customer->update([
                        "points" =>  $current_point
                    ]);
                }
            }

            if ($type == PointCustomerUtils::GIFT_AT_SPIN_WHEEL) {

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);

                if ($customer != null) {
                    $customer->update([
                        "points" =>  $current_point
                    ]);
                }
            }
        } catch (Exception $e) {
        }
    }
}
