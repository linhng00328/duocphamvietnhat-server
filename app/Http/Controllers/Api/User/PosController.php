<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CollaboratorUtils;
use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationUserJob;
use App\Jobs\SendEmailOrderCustomerJob;
use App\Models\CcartItem;
use App\Models\LineItem;
use App\Models\MsgCode;
use App\Models\Order;
use App\Helper\PointCustomerUtils;
use App\Jobs\PushNotificationStaffJob;
use App\Models\Agency;
use App\Models\Collaborator;
use App\Models\Customer;
use App\Models\RevenueExpenditure;
use App\Services\BalanceCustomerService;
use Illuminate\Http\Request;

class PosController extends Controller
{

    function calcute($request)
    {
        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->first();

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        if ($orderExists->order_status ==  StatusDefineCode::CUSTOMER_HAS_RETURNS && !$request->is_customer_has_returns) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::REFUNDED[0],
                'msg' => MsgCode::REFUNDED[1],
            ], 400);
        }

        //Phieu  chi (kiểm tra đã thực hiện giao hàng và xuất phiếu chi)
        $lastEX =   RevenueExpenditure::where(
            'references_value',
            $orderExists->order_code,
        )->where(
            "store_id",
            $orderExists->store_id
        )
            ->where('action_create',  RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_EXPENDITURE,)
            ->where('is_revenue', false)->first();


        // if ( ($orderExists->order_status !=  StatusDefineCode::COMPLETED && $orderExists->order_status !=  StatusDefineCode::SHIPPING) &&  $lastEX == null) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::INCOMPLETE_ORDERS_NON_REFUNDABLE[0],
        //         'msg' => MsgCode::INCOMPLETE_ORDERS_NON_REFUNDABLE[1],
        //     ], 400);
        // }


        //Lấy 2 danh sách để xử lý
        $refund_line_items = [];
        $arr_id_line_items = $orderExists->line_items->pluck('id')->toArray();



        foreach ($orderExists->line_items->toArray() as $item) {
            //Danh sách giá
            $arr_id_with_item_price[$item['id']] = $item['item_price'];
            //Danh sách tổng
            $arr_id_with_quantity[$item['id']] = $item['quantity'];
            //Danh số lượng đã hoàn
            $arr_id_with_total_refund[$item['id']] = $item['total_refund'];
            //Lấy id sản phẩm
            $arr_id_with_product_id[$item['id']] = $item['product']['id'];
            //Số lượng có thể hoàn
            $arr_id_with_can_refund[$item['id']] = $item['quantity'] - $item['total_refund'];
            //Số lượng còn lại sau khi hoàn
            $arr_id_with_reaming_after_refund[$item['id']] = $arr_id_with_can_refund[$item['id']];
        }


        if ($request->refund_line_items == null || count($request->refund_line_items) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                'msg' => MsgCode::NO_LINE_ITEMS[1],
            ], 400);
        }

        if ($request->refund_line_items != null && count($request->refund_line_items) > 0) {
            foreach ($request->refund_line_items  as $line_item_refund) {

                if (!in_array($line_item_refund['line_item_id'], $arr_id_line_items)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                        'msg' => MsgCode::NO_LINE_ITEMS[1],
                    ], 400);
                }

                $quantity_after_refund =
                    $arr_id_with_quantity[$line_item_refund['line_item_id']]  -
                    ($line_item_refund['quantity'] + $arr_id_with_total_refund[$line_item_refund['line_item_id']]);

                $arr_id_with_reaming_after_refund[$line_item_refund['line_item_id']] = $quantity_after_refund;

                if ($quantity_after_refund  < 0) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::REFUND_AMOUNT_CANNOT_GREATER_THAN_CURRENT_QUANTITY[0],
                        'msg' => MsgCode::REFUND_AMOUNT_CANNOT_GREATER_THAN_CURRENT_QUANTITY[1],
                    ], 400);
                }
            }
        }


        $is_refund_part = false;
        $is_last_refund = true;

        //KIểm tra có phải toàn toàn bộ không
        if (count($orderExists->line_items->toArray()) !=  count($request->refund_line_items)) {
            $is_refund_part = true;
        } else {
            foreach ($orderExists->line_items->toArray() as $item) {
                //Số lượng còn lại sau khi hoàn
                if ($arr_id_with_reaming_after_refund[$item['id']] > 0) {
                    $is_refund_part = true;
                }
            }
            foreach ($orderExists->line_items->toArray() as $item) {
                //Số lượng còn lại sau khi hoàn
                if ($arr_id_with_total_refund[$item['id']] > 0) {
                    $is_refund_part = true;
                }
            }
        }

        foreach ($orderExists->line_items->toArray() as $item) {
            //Số lượng còn lại sau khi hoàn
            $reman = $arr_id_with_reaming_after_refund[$item['id']];
            if ($reman  >   0) {
                $is_last_refund = false;
            }
        }
        if ($orderExists->total_money_refund == 0 && $is_last_refund  == true) {
            $is_last_refund = false;
        }


        $usePromotion = 0;
        $list_use_discount_amout = "";
        if ($orderExists->combo_discount_amount > 0) {
            $list_use_discount_amout = $list_use_discount_amout . "Combo, ";
            $usePromotion += 1;
        }
        if ($orderExists->discount > 0) {
            $list_use_discount_amout = $list_use_discount_amout . "Chiết khấu, ";
            $usePromotion += 1;
        }
        if ($orderExists->voucher_discount_amount > 0) {
            $list_use_discount_amout = $list_use_discount_amout . "Voucher, ";
            $usePromotion += 1;
        }
        if ($usePromotion > 1 &&   $is_refund_part == true) {
            $list_use_discount_amout = rtrim($list_use_discount_amout, ", ");

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng sử dụng hơn 1 chương trình giảm giá (" . $list_use_discount_amout . ") không thể hoàn một phần, hãy hoàn toàn bộ và lên đơn mới",
            ], 400);
        }


        if ($orderExists->customer_used_bonus_products != null &&   $is_refund_part == true) {
            $customer_used_bonus_products = $orderExists->customer_used_bonus_products;
            if (is_array($customer_used_bonus_products) &&  count($customer_used_bonus_products) > 0 &&   $is_refund_part == true) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng có thưởng sản phầm không thể hoàn 1 phần",
                ], 400);
            }
        }

        if ($orderExists->agency_by_customer_id != null &&   $is_refund_part == true) {
            $list_use_discount_amout = rtrim($list_use_discount_amout, ", ");

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đại lý không thể hoàn 1 phần",
            ], 400);
        }

        if ($orderExists->collaborator_by_customer_id != null  &&   $is_refund_part == true) {
            $list_use_discount_amout = rtrim($list_use_discount_amout, ", ");

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng cộng tác viên không thể hoàn 1 phần",
            ], 400);
        }



        //Số tiền có thể hoàn
        $total_final_can_refund = $orderExists->total_final -  $orderExists->total_money_refund;


        //Tổng tiền còn lại
        $total_final_reamin = 0;
        foreach ($orderExists->line_items->toArray() as $item) {
            $quantity_reamin = $arr_id_with_reaming_after_refund[$item['id']] ?? 0;
            $item_price = $arr_id_with_item_price[$item['id']] ?? 0;

            $total_final_reamin += ($quantity_reamin * $item_price);
        }



        ///số tiền hoàn đơn hiện tại
        $total_refund_current_in_time = 0;
        if ($request->refund_line_items != null && count($request->refund_line_items) > 0) {
            foreach ($request->refund_line_items  as $line_item_refund) {

                $line_item_id = $line_item_refund['line_item_id'];
                $quantity_new =  $line_item_refund['quantity'];

                $item_price =  $arr_id_with_item_price[$line_item_id];

                $total_refund_current_in_time +=  $item_price * $quantity_new;
            }
        }


        $discount = 0;
        $voucher_discount_amount = 0;


        //nếu là lần cuối hoàn thì lấy hết số sp để check voucher
        if ($is_last_refund  == true) {
            $total_refund_current_in_time =    $total_final_can_refund;
        } else {
            // ko phải lần cuối và không phải hoàn 1 phần
            if ($is_refund_part  == false) {
                $discount  = $orderExists->discount;
                $voucher_discount_amount = $orderExists->voucher_discount_amount;
                $total_refund_current_in_time =    $total_final_can_refund;
            } else {
                $discount = 0;
                $voucher_discount_amount = 0;

                /// kiểm tra combo
                if ($orderExists->combo_discount_amount > 0) {

                    //Check sp trong combo
                    $listIdProduct = [];
                    foreach ($orderExists->line_items->toArray() as $item) {
                        $item_price =    $arr_id_with_item_price[$item['id']];
                        $quantity =   $arr_id_with_reaming_after_refund[$item['id']];
                        $product_id =  $arr_id_with_product_id[$item['id']];

                        if ($quantity > 0) {
                            if (isset($listIdProduct[$product_id])) {

                                $after_quantity =   $listIdProduct[$product_id]['quantity'];
                                $new_quantity =  $quantity;

                                $after_price = $listIdProduct[$product_id]['price_or_discount'];
                                $new_price =   $item_price;

                                $avg_price =   $after_price;
                                $total_quantity =    $after_quantity;

                                if ($new_price >  $after_price) {
                                    $avg_price = $new_price;
                                    $total_quantity = $new_quantity;
                                }

                                $listIdProduct[$product_id] = [
                                    "id"  => $product_id,
                                    "quantity" => $total_quantity,
                                    "price_or_discount" => $avg_price
                                ];
                            } else {
                                $listIdProduct[$product_id] = [
                                    "id"  =>  $product_id,
                                    "quantity" => $quantity,
                                    "price_or_discount" =>  $item_price
                                ];
                            }
                        }
                    }



                    $combo_discount_amount = 0;
                    if ($orderExists->customer_used_combos != null) {

                        $Combos = $orderExists->customer_used_combos;

                        $Combos = json_decode(json_encode($Combos), FALSE);
                        foreach ($Combos as $combo) {
                            $combo = $combo->combo;
                            $productValid = 0;
                            $lengthProductCombo = count($combo->products_combo);
                            $totalMoney = 0;
                            $multiplier = null; //hệ số nhân combo

                            //Chạy các combo đã sử dụng
                            foreach ($combo->products_combo as $product_combo) {
                                if (isset($listIdProduct[$product_combo->product->id])) {

                                    //kiem tra product va combo quantity > 0
                                    if ($product_combo->quantity == 0 || $listIdProduct[$product_combo->product->id]['quantity'] == 0) {
                                        break;
                                    }


                                    $productValid++;
                                    $totalMoney += $listIdProduct[$product_combo->product->id]['price_or_discount'] * $product_combo->quantity;
                                    //tinh ho so nhan moi
                                    $mul = (int)($listIdProduct[$product_combo->product->id]['quantity'] / $product_combo->quantity);

                                    if ($multiplier === null) {
                                        $multiplier = $mul;
                                    }

                                    if (($mul < $multiplier && $multiplier != null) == true) {
                                        $multiplier = $mul;
                                    };
                                } else {
                                    break;
                                }
                            }

                            if ($lengthProductCombo == $productValid && $multiplier != 0) {

                                if ($combo->discount_type == 0) {
                                    if ($combo->value_discount <=  $totalMoney) {
                                        //cong vao gia tien khuyen mai

                                        $combo_discount_amount += ($combo->value_discount) * $multiplier;
                                    }
                                }
                                if ($combo->discount_type == 1) {

                                    $totalDiscounnt = $totalMoney * ($combo->value_discount / 100);
                                    if ($totalDiscounnt <= $totalMoney) {
                                        $combo_discount_amount += ($totalDiscounnt) * $multiplier;
                                    }
                                }
                            }
                        }
                    }



                    $total_refund_current_in_time =  $total_final_can_refund -  ($total_final_reamin - $combo_discount_amount);
                }

                /// kiểm tra voucher
                if ($orderExists->customer_used_voucher != null) {
                    $totalAvalibleForVoucher = 0;

                    $product_in_voucher = [];

                    $customer_used_voucher_products = $orderExists->customer_used_voucher->products ?? null;
                    if ($customer_used_voucher_products) {
                        foreach ($orderExists->customer_used_voucher->products as $product) {
                            $product_in_voucher[$product->id] = true;
                        }
                    }

                    //giảm giá theo phần trăm
                    if ($orderExists->customer_used_voucher->discount_type == 1) {


                        if ($orderExists->customer_used_voucher->value_discount > 0) {
                            $totalDiscounnt = 0;  //Tính số tiền được giảm của các sản phẩm còn lại sau khi hoàn
                            /////từ đó lấy tổng đã thanh toán trừ đi sp còn lại trừ là ra số tiền hoàn
                            /////////////
                            if ($orderExists->customer_used_voucher->voucher_type == 0) { //tat ca san pham
                                foreach ($orderExists->line_items->toArray() as $item) {
                                    $item_price =    $arr_id_with_item_price[$item['id']];
                                    $quantity =   $arr_id_with_reaming_after_refund[$item['id']];

                                    $totalAvalibleForVoucher += $item_price * $quantity;
                                }

                                $percent = ($orderExists->customer_used_voucher->value_discount / 100);
                                $totalDiscounnt = ($totalAvalibleForVoucher) * $percent;
                            } else { //mot so san pham trong voucher

                                foreach ($orderExists->line_items->toArray() as $item) {
                                    $product_id =  $arr_id_with_product_id[$item['id']];

                                    $item_price =    $arr_id_with_item_price[$item['id']];
                                    $quantity =   $arr_id_with_reaming_after_refund[$item['id']];


                                    if (isset($product_in_voucher[$product_id])) {
                                        $percent = ($orderExists->customer_used_voucher->value_discount / 100);
                                        $totalDiscounnt += ($item_price * $percent)  *  $quantity;

                                        $totalAvalibleForVoucher += $item_price * $quantity;
                                    }
                                }
                            }



                            if ($totalDiscounnt > $orderExists->customer_used_voucher->max_value_discount && $orderExists->customer_used_voucher->set_limit_value_discount == true) {
                                $totalDiscounnt = $orderExists->customer_used_voucher->max_value_discount;
                            }

                            //nếu tổng lớn hơn limit thì thỏa sử dụng voucher
                            if ($total_final_reamin >= $orderExists->customer_used_voucher->value_limit_total) {
                                $total_refund_current_in_time =  $total_final_can_refund -  ($total_final_reamin - $totalDiscounnt);
                            } else {
                                $total_refund_current_in_time  =  $total_final_can_refund - $total_final_reamin;
                            }
                        }
                    }

                    //giảm giá theo số tiền
                    if ($orderExists->customer_used_voucher->discount_type == 0) {


                        if ($orderExists->customer_used_voucher->value_discount > 0) {

                            $totalDiscounnt = 0;  //Tính số tiền được giảm của các sản phẩm còn lại sau khi hoàn
                            /////từ đó lấy tổng đã thanh toán trừ đi sp còn lại trừ là ra số tiền hoàn
                            /////////////
                            if ($orderExists->customer_used_voucher->voucher_type == 0) { //tat ca san pham
                                foreach ($orderExists->line_items->toArray() as $item) {
                                    $item_price =    $arr_id_with_item_price[$item['id']];
                                    $quantity =   $arr_id_with_reaming_after_refund[$item['id']];

                                    $totalAvalibleForVoucher += $item_price * $quantity;
                                }

                                $percent = ($orderExists->customer_used_voucher->value_discount / 100);
                                $totalDiscounnt = ($totalAvalibleForVoucher) * $percent;
                            } else { //mot so san pham trong voucher

                                foreach ($orderExists->line_items->toArray() as $item) {
                                    $product_id =  $arr_id_with_product_id[$item['id']];

                                    $item_price =    $arr_id_with_item_price[$item['id']];
                                    $quantity =   $arr_id_with_reaming_after_refund[$item['id']];


                                    if (isset($product_in_voucher[$product_id])) {
                                        $percent = ($orderExists->customer_used_voucher->value_discount / 100);
                                        $totalDiscounnt += $item_price *  $quantity;

                                        $totalAvalibleForVoucher += $item_price * $quantity;
                                    }
                                }
                            }


                            //Nếu còn lại
                            if ($totalAvalibleForVoucher >= $orderExists->customer_used_voucher->value_limit_total) {
                                $totalDiscounnt =   $orderExists->customer_used_voucher->value_discount;
                            } else {
                                $totalDiscounnt =   0;
                            }


                            $total_refund_current_in_time =  $total_final_can_refund -  ($total_final_reamin - $totalDiscounnt);
                        }
                    }
                }


                /// kiểm tra chiết khấu
                if ($orderExists->discount > 0) {
                    $percent = ($orderExists->discount / ($orderExists->total_after_discount + $orderExists->discount));
                    $total_refund_current_in_time  = $total_refund_current_in_time * (1 - $percent);
                }
            }
        }


        // //Kiểm tra xu
        // if ($is_refund_part == true) { //Nếu hoàn 1 phần

        //     if ($is_last_refund == false) { //ko phải nếu là lần cuối
        //         $total_refund_current_in_time =  $total_refund_current_in_time;
        //     }
        //     if ($is_last_refund == true) { //nếu là lần cuối
        //         $total_refund_current_in_time =  $total_refund_current_in_time + $orderExists->bonus_points_amount_used;
        //     }
        // }

        RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);
        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);
        return [
            'total_refund_current_in_time' =>   $total_refund_current_in_time,
            'is_refund_part' => $is_refund_part,
            'voucher_discount_amount' => $voucher_discount_amount,
            'discount' => $discount,
            'arr_id_with_product_id' => $arr_id_with_product_id,
            'is_last_refund' => $is_last_refund
        ];
    }

    /**
     *  Tính tiền hoàn
     *  @bodyParam  string Mã đơn hàng hoàn trả 
     *  @bodyParam  List danh sách refund_line_items cần giữ lại list[]
     */
    public function calculate_money_refund(Request $request)
    {
        $data =  $this->calcute($request);

        if (isset(json_decode(json_encode($data), true)['original'])) {
            return  $data;
        };


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_refund_current_in_time" => $data['total_refund_current_in_time'],
                "is_refund_part" => $data['is_refund_part'],
                "voucher_discount_amount" => $data['voucher_discount_amount'],
                "discount" => $data['discount'],
            ]
        ], 200);
    }
    /**
     * Hoàn sản phẩm hoặc đổi trả, hoàn tiền
     *  @bodyParam  string Mã đơn hàng hoàn trả 
     *  @bodyParam  List danh sách refund_line_items cần giữ lại list[]
     */
    public function refund(Request $request, $is_customer_has_returns = null)
    {

        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->first();
        $total_refund_current_in_time = $orderExists->total_final;

        if ($is_customer_has_returns === true) {
            $refund_line_items = count($orderExists->line_items ?? []) > 0 ? array_map(function ($item) {
                return [
                    'line_item_id' => $item['id'],
                    'quantity' => $item['quantity'] - $item['total_refund'] > 0 ? $item['quantity'] - $item['total_refund'] : 0,
                ];
            }, $orderExists->line_items()->get()->toArray()) : [];
            $request->merge([
                'is_customer_has_returns' => $is_customer_has_returns,
                'refund_line_items' => $refund_line_items
            ]);
        }

        $data =  $this->calcute($request);

        if (isset(json_decode(json_encode($data), true)['original'])) {
            return  $data;
        };

        $total_refund_current_in_time = $data['total_refund_current_in_time'];
        $is_refund_part =  $data['is_refund_part'];
        $is_last_refund =  $data['is_last_refund'];
        $voucher_discount_amount = $data['voucher_discount_amount'];
        $discount =  $data['discount'];
        $arr_id_with_product_id = $data['arr_id_with_product_id'];


        if ($total_refund_current_in_time > ($orderExists->total_final - $orderExists->total_money_refund)) {
            $total_refund_current_in_time = $orderExists->total_final - $orderExists->total_money_refund;
        };



        if ($request->staff != null) {
            CcartItem::where(
                'store_id',
                $request->store->id
            )->where(
                'staff_id',
                $request->staff->id
            )->delete();
        } else
        if ($request->user != null) {
            CcartItem::where(
                'store_id',
                $request->store->id
            )->where(
                'user_id',
                $request->user->id
            )->delete();
        }


        $orderExists->update([
            'total_money_refund' =>  $orderExists->total_money_refund + $total_refund_current_in_time
        ]);


        $order_code = Helper::getRandomOrderString();

        $orderNew = $orderExists->replicate();
        $orderNew->order_code =  $order_code;
        $orderNew->remaining_amount =  0;
        $orderNew->save();


        $line_items_at_time = $orderNew->line_items_at_time;

        $line_items_at_time_new = [];

        $total_before_discount = 0;
        $total_after_discount = 0;
        $total_final = 0;

        $total_cost_of_capital = 0;

        if ($request->refund_line_items != null && count($request->refund_line_items) > 0) {
            foreach ($request->refund_line_items  as $line_item_refund) {

                $line_item_id = $line_item_refund['line_item_id'];
                $quantity_new =  $line_item_refund['quantity'];

                $item_line_in_time_new =     $this->new_line_item_in_time($line_items_at_time, $arr_id_with_product_id[$line_item_id],  $quantity_new);

                if ($item_line_in_time_new != null) {
                    array_push($line_items_at_time_new,  $item_line_in_time_new);
                }

                $lineItemBefore = LineItem::where('id', $line_item_id)->first();
                $lineItemBefore->update([
                    "total_refund" => $lineItemBefore->total_refund +  $quantity_new
                ]);

                $cost_of_capital = $lineItemBefore->cost_of_capital;
                $total_cost_of_capital += ($cost_of_capital * $quantity_new);

                InventoryUtils::add_sub_stock_by_id(
                    $request->store->id,
                    $orderExists->branch_id,
                    $lineItemBefore->product_id,
                    $lineItemBefore->element_distribute_id,
                    $lineItemBefore->sub_element_distribute_id,
                    $line_item_refund['quantity'],
                    InventoryUtils::TYPE_REFUND_ORDER,
                    $orderNew->id,
                    $orderNew->order_code
                );

                $total_before_discount += $lineItemBefore->before_discount_price * $quantity_new;
                $total_after_discount += $lineItemBefore->item_price * $quantity_new;

                $itemLineNew = $lineItemBefore->replicate();
                $itemLineNew->order_id = $orderNew->id;
                $itemLineNew->quantity = $quantity_new;
                $itemLineNew->is_refund = true;
                $itemLineNew->save();
            }
        }

        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Shop ' . $request->store->name,
            'Đơn hàng ' . ($orderExists->order_code) . ' vừa được hoàn',
            TypeFCM::REFUND_ORDER,
            $orderNew->order_code,
            $orderNew->branch_id,
        );


        PushNotificationStaffJob::dispatch(
            $request->store->id,
            'Shop ' . $request->store->name,
            'Đơn hàng ' . ($orderExists->order_code) . ' vừa được hoàn',
            TypeFCM::REFUND_ORDER,
            $orderNew->order_code,
            $orderNew->branch_id,
            null,
        );


        //Đòi lại xu
        if ($orderNew->points_awarded_to_customer > 0) {
            $percent = $orderExists->total_after_discount ? $total_refund_current_in_time / $orderExists->total_after_discount : 0;
            $point_sub = - ($orderNew->points_awarded_to_customer *  $percent);

            PointCustomerUtils::add_sub_point(
                PointCustomerUtils::REFUND_ORDER,
                $request->store->id,
                $orderExists->customer_id,
                round($point_sub),
                $orderExists->id,
                $orderExists->order_code
            );

            $customer = Customer::where("id", $orderExists->customer_id)->first();

            if ($customer) {
                $current_point = $customer->points ?? 0;

                PushNotificationCustomerJob::dispatch(
                    $request->store->id,
                    $orderExists->customer_id,
                    'Shop ' . $request->store->name,
                    'Đơn hàng ' . $orderExists->order_code . ' vừa được hoàn ' . round($orderNew->points_awarded_to_customer *  $percent) . ' xu. Số xu hiện tại: ' . number_format($current_point, 0, ',', '.') . ' xu',
                    TypeFCM::REFUND_POINT,
                    null
                );
            }
        }




        //Hoàn hết
        if ($is_refund_part  == false || $is_customer_has_returns) {

            if ($orderExists->is_handled_balance_collaborator == true) {
                if ($orderExists->collaborator_by_customer_id != null) {
                    BalanceCustomerService::change_balance_collaborator(
                        $request->store->id,
                        $orderExists->collaborator_by_customer_id,
                        BalanceCustomerService::ORDER_REFUND_CTV,
                        -$orderExists->share_collaborator,
                        $orderNew->id,
                        $orderExists->order_code,
                        'Trừ ' . $orderExists->share_collaborator . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code
                    );

                    $collaborator  = Collaborator::where('store_id', $request->store->id)
                        ->where('customer_id', $orderExists->collaborator_by_customer_id)
                        ->first();

                    if ($collaborator) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $orderExists->collaborator_by_customer_id,
                            'Shop ' . $request->store->name,
                            'Trừ ' . $orderExists->share_collaborator . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code . '. Số dư hiện tại: ' . $collaborator->balance . 'đ',
                            TypeFCM::REFUND_COMMISSION,
                            null
                        );
                    }
                }

                if ($orderExists->collaborator_by_customer_referral_id != null) {
                    BalanceCustomerService::change_balance_collaborator(
                        $request->store->id,
                        $orderExists->collaborator_by_customer_referral_id,
                        BalanceCustomerService::ORDER_REFUND_CTV,
                        -$orderExists->share_collaborator_referen,
                        $orderNew->id,
                        $orderExists->order_code,
                        'Trừ ' . $orderExists->share_collaborator_referen . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code
                    );

                    $collaborator  = Collaborator::where('store_id', $request->store->id)
                        ->where('customer_id', $orderExists->collaborator_by_customer_referral_id)
                        ->first();

                    if ($collaborator) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $orderExists->collaborator_by_customer_referral_id,
                            'Shop ' . $request->store->name,
                            'Trừ ' . $orderExists->share_collaborator_referen . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code . '. Số dư hiện tại: ' . $collaborator->balance . 'đ',
                            TypeFCM::REFUND_COMMISSION,
                            null
                        );
                    }
                }
            }

            if ($orderExists->is_handled_balance_agency == true) {
                if ($orderExists->agency_ctv_by_customer_id != null) {
                    BalanceCustomerService::change_balance_agency(
                        $request->store->id,
                        $orderExists->agency_ctv_by_customer_id,
                        BalanceCustomerService::ORDER_REFUND_CTV,
                        -$orderExists->share_agency,
                        $orderNew->id,
                        $orderExists->order_code,
                        'Trừ ' . $orderExists->share_agency . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code
                    );

                    $agency  = Agency::where('store_id', $request->store->id)
                        ->where('customer_id', $orderExists->agency_ctv_by_customer_id)
                        ->first();

                    if ($agency) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $orderExists->agency_ctv_by_customer_id,
                            'Shop ' . $request->store->name,
                            'Trừ ' . $orderExists->share_agency . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code . '. Số dư hiện tại: ' . $agency->balance . 'đ',
                            TypeFCM::REFUND_COMMISSION,
                            null
                        );
                    }
                }

                if ($orderExists->agency_ctv_by_customer_referral_id != null) {
                    BalanceCustomerService::change_balance_agency(
                        $request->store->id,
                        $orderExists->agency_ctv_by_customer_referral_id,
                        BalanceCustomerService::ORDER_REFUND_CTV,
                        -$orderExists->share_agency_referen,
                        $orderNew->id,
                        $orderExists->order_code,
                        'Trừ ' . $orderExists->share_agency_referen . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code
                    );

                    $agency  = Agency::where('store_id', $request->store->id)
                        ->where('customer_id', $orderExists->agency_ctv_by_customer_referral_id)
                        ->first();

                    if ($agency) {
                        PushNotificationCustomerJob::dispatch(
                            $request->store->id,
                            $orderExists->agency_ctv_by_customer_referral_id,
                            'Shop ' . $request->store->name,
                            'Trừ ' . $orderExists->share_agency_referen . 'đ tiền hoàn đơn hàng ' . $orderExists->order_code . '. Số dư hiện tại: ' . $agency->balance . 'đ',
                            TypeFCM::REFUND_COMMISSION,
                            null
                        );
                    }
                }
            }
            // if ($orderExists->is_handled_balance_collaborator == true) {
            //     if ($orderExists->collaborator_by_customer_id != null) {

            //         BalanceCustomerService::change_balance_collaborator(
            //             $request->store->id,
            //             $orderExists->collaborator_by_customer_id,
            //             BalanceCustomerService::ORDER_REFUND_CTV,
            //             -$orderExists->share_collaborator,
            //             $orderNew->id,
            //             $orderExists->order_code,
            //         );

            //         $collaborator  = Collaborator::where('store_id', $request->store->id)
            //             ->where('customer_id', $orderExists->collaborator_by_customer_id)
            //             ->first();

            //         if ($collaborator) {
            //             PushNotificationCustomerJob::dispatch(
            //                 $request->store->id,
            //                 $orderExists->collaborator_by_customer_id,
            //                 'Shop ' . $request->store->name,
            //                 'Đơn hàng ' . $orderExists->order_code . ' vừa được hoàn ' . $orderExists->share_collaborator . 'đ. Số dư hiện tại: ' . $collaborator->balance . 'đ',
            //                 TypeFCM::REFUND_COMMISSION,
            //                 null
            //             );
            //         }
            //     } else {

            //         //Tìm khách hàng chứa sdt giới thiệu
            //         $customer_order = Customer::where('store_id', $request->store->id)
            //             ->where('id', $orderExists->customer_id)->first();

            //         if ($customer_order  != null && $customer_order->referral_phone_number != null) {
            //             //cus này đăng ký trước và gt cus mua đơn hàng này
            //             $customer_referral = Customer::where('phone_number', $customer_order->referral_phone_number)->where(
            //                 'store_id',
            //                 $request->store->id
            //             )->first();
            //             if ($customer_referral != null && CollaboratorUtils::isCollaborator($customer_referral->id, $request->store->id) == true) {
            //                 BalanceCustomerService::change_balance_collaborator(
            //                     $request->store->id,
            //                     $customer_referral->id,
            //                     BalanceCustomerService::ORDER_REFUND_CTV,
            //                     -$orderExists->share_collaborator,
            //                     $orderExists->id,
            //                     $orderExists->order_code,
            //                 );

            //                 $collaborator  = Collaborator::where('store_id', $request->store->id)
            //                     ->where('customer_id', $customer_referral->id)
            //                     ->first();

            //                 if ($collaborator) {
            //                     PushNotificationCustomerJob::dispatch(
            //                         $request->store->id,
            //                         $customer_referral->id,
            //                         'Shop ' . $request->store->name,
            //                         'Đơn hàng ' . $orderExists->order_code . ' vừa được hoàn ' . $orderExists->share_collaborator . 'đ. Số dư hiện tại: ' . $collaborator->balance . 'đ',
            //                         TypeFCM::REFUND_COMMISSION,
            //                         null
            //                     );
            //                 }
            //             }
            //         }
            //     }
            // }

            $orderNew->update(
                [
                    "order_code_refund" => $orderExists->order_code,
                    "order_status" => StatusDefineCode::CUSTOMER_HAS_RETURNS,
                    "payment_status" => StatusDefineCode::PAY_REFUNDS,
                    "share_collaborator" =>  $orderExists->share_collaborator > 0 ?  -$orderExists->share_collaborator : $orderExists->share_collaborator
                ]
            );
        } else {

            //không hoàn hết và lần cuối hoàn
            if ($is_last_refund == true) {
                $orderNew->update(
                    [
                        "order_code_refund" => $orderExists->order_code,
                        "order_status" => StatusDefineCode::CUSTOMER_HAS_RETURNS,
                        "payment_status" => StatusDefineCode::PAY_REFUNDS,
                        "total_before_discount" => $total_refund_current_in_time,
                        "total_after_discount" => $total_refund_current_in_time,
                        "line_items_in_time" => json_encode($line_items_at_time_new),
                        "discount" => 0,
                        "voucher_discount_amount" => 0,
                        "bonus_points_amount_used" => 0,
                        "combo_discount_amount" => 0,
                        "total_shipping_fee" => 0,
                        "total_final" =>  $total_refund_current_in_time,
                        "used_discount" => json_encode([]),
                        "used_combos" => json_encode([]),
                        "used_combos" => null,
                        "total_cost_of_capital" => $total_cost_of_capital,
                        "share_collaborator" =>  $orderExists->share_collaborator > 0 ?  -$orderExists->share_collaborator : $orderExists->share_collaborator
                    ]
                );
            } else {
                $orderNew->update(
                    [
                        "order_code_refund" => $orderExists->order_code,
                        "order_status" => StatusDefineCode::CUSTOMER_HAS_RETURNS,
                        "payment_status" => StatusDefineCode::PAY_REFUNDS,
                        "line_items_in_time" => json_encode($line_items_at_time_new),
                        "total_before_discount" => $total_refund_current_in_time,
                        "total_after_discount" => $total_refund_current_in_time,
                        "discount" => 0,
                        "voucher_discount_amount" => 0,
                        "bonus_points_amount_used" => 0,
                        "combo_discount_amount" => 0,
                        "total_shipping_fee" => 0,
                        "total_final" =>  $total_refund_current_in_time,
                        "used_discount" => json_encode([]),
                        "used_combos" => json_encode([]),
                        "used_combos" => null,
                        "total_cost_of_capital" => $total_cost_of_capital,
                        "share_collaborator" =>  $orderExists->share_collaborator > 0 ?  -$orderExists->share_collaborator : $orderExists->share_collaborator
                    ]
                );
            }
        }




        //Thêm phiếu
        if ($total_refund_current_in_time > 0 && $orderExists->customer_id != null) {
            RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_OTHER_INCOME,
                RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER,
                $orderExists->customer_id,
                null,
                $orderNew->id,
                $orderNew->order_code,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_REFUND_REVENUE,
                $total_refund_current_in_time,
                true,
                "Tạo phiếu thu từ khách hàng trả hàng",
                RevenueExpenditureUtils::PAYMENT_TYPE_CASH
            );

            RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_OTHER_INCOME,
                RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER,
                $orderExists->customer_id,
                null,
                $orderNew->id,
                $orderNew->order_code,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_REFUND_EXPENDITURE,
                $total_refund_current_in_time,
                false,
                "Tạo phiếu chi trả tiền hoàn hàng",
                RevenueExpenditureUtils::PAYMENT_TYPE_CASH
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function new_line_item_in_time($line_items_at_tim_after, $product_id, $quantity)
    {
        $item = null;
        foreach ($line_items_at_tim_after as $item_after) {
            if ($item_after->id == $product_id) {
                $item =  $item_after;
                break;
            }
        }
        if ($item  != null) {
            $item->quantity = $quantity;
        }


        return $item;
    }

    /**
     * Xóa sản phẩm trong giỏ hàng
     */
    public function clearCart(Request $request)
    {

        $oneCart = CartController::get_one_cart_default($request);

        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        CcartItem::where(
            'list_cart_id',
            $oneCart->id
        )->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Gửi email hóa đơn
     * @bodyParam order_code string mã hóa đơn
     * @bodyParam email string email gửi tới
     */
    public function sendOrderToEmail(Request $request)
    {

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_EMAIL[0],
                'msg' => MsgCode::INVALID_EMAIL[1],
            ], 404);
        }

        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->with('line_items')
            ->first();


        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        //Gửi email
        $emails = [$request->email];
        SendEmailOrderCustomerJob::dispatch(
            $emails,
            $request->store,
            $orderExists->order_code
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
