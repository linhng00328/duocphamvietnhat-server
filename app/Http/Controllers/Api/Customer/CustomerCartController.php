<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\AgencyUtils;
use App\Helper\BranchUtils;
use App\Helper\CollaboratorUtils;
use App\Helper\CustomerUtils;
use App\Helper\GroupCustomerUtils;
use App\Helper\Helper;
use App\Helper\ProductUtils;
use App\Helper\VoucherUtils;
use App\Http\Controllers\Api\User\ConfigShipController;
use App\Http\Controllers\Api\User\GeneralSettingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\BonusAgency;
use App\Models\BonusAgencyStep;
use App\Models\BonusProduct;
use App\Models\BonusProductItem;
use App\Models\BonusProductItemLadder;
use App\Models\CcartItem;
use App\Models\Collaborator;
use App\Models\Combo;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\ListCart;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\PayAgency;
use App\Models\PayCollaborator;
use App\Models\PointSetting;
use App\Models\Product;
use App\Models\ProductRetailStep;
use App\Models\Shipment;
use App\Models\StoreAddress;
use App\Models\SubElementDistribute;
use App\Models\Voucher;
use App\Services\ShipperService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * @group  Customer/Giỏ hàng
 */

class CustomerCartController extends Controller
{


    static public function data_response($allCart, $request, $oneCart = null)
    {
        $code_voucher =  $request->code_voucher;
        $is_use_points = $request->is_use_points;
        $is_use_balance_collaborator = $request->is_use_balance_collaborator;
        $is_use_balance_agency = $request->is_use_balance_agency;
        $discount = $request->discount;
        $total_shipping_fee = $request->total_shipping_fee;
        $is_order_for_customer = $request->is_order_for_customer ?: false;

        //Lưu dữ liệu xu đã sử dụng đơn đã đặt
        $points_amount_used_edit_order = 0;
        $points_total_used_edit_order = 0;
        $total_shipping_discount_amount = 0;
        $customer  = null;
        if ($oneCart != null) {
            $code_voucher = $oneCart->code_voucher ?? "";
            $is_use_points = $oneCart->is_use_points ?? null;
            $is_use_balance_collaborator = $oneCart->is_use_balance_collaborator;
            $is_use_balance_agency = $oneCart->is_use_balance_agency;
            $discount = $oneCart->discount ?? 0;
            $total_shipping_fee = $oneCart->total_shipping_fee ?? 0;
            $total_shipping_discount_amount = $oneCart->ship_discount_amount ?? 0;
            if ($total_shipping_fee != 0) {
                $total_shipping_fee += $total_shipping_discount_amount;
            }

            $points_amount_used_edit_order = $oneCart->points_amount_used_edit_order ?? 0;
            $points_total_used_edit_order = $oneCart->points_total_used_edit_order ?? 0;


            if (!empty($oneCart->customer_id)) {
                $customer = Customer::where('store_id', $request->store->id)->where('id', $oneCart->customer_id)->first();
            } else if (!empty($oneCart->customer_phone)) {
                $customer = Customer::where('store_id', $request->store->id)->where('phone_number', $oneCart->customer_phone)->first();
            }
        }


        if ($customer  == null) {
            $customer = $request->customer;

            if ($customer == null) {
                $request = request();
                $customer = request('customer', $default = null);
            }
        }

        $total_before_discount = 0;

        $balance_collaborator_can_use = 0; //số dư trong CTV
        $balance_collaborator_used = 0; //số dư CTV đã sử dụng
        $balance_collaborator_used_before = 0; //số dư CTV đã sử dụng trước đó

        $balance_agency_can_use = 0; //số dư trong CTV
        $balance_agency_used = 0; //số dư CTV đã sử dụng
        $balance_agency_used_before = 0; // số dư Đại lý đã sử dụng trước đó

        $bonus_points_amount_can_use = 0; //tiền trừ điểm thưởng có thể sử dụng
        $total_points_can_use = 0; // điểm thưởng  có thể sử dụng

        $total_points_used = 0; //điểm đã sử dụng
        $bonus_points_amount_used = 0; //tiền trừ điểm thưởng đã sử dụng

        $combo_discount_amount = 0;
        $product_discount_amount = 0;
        $product_discount_amount_override = 0;
        $voucher_discount_amount = 0;
        $total_after_discount = 0;
        $share_collaborator = 0;
        $share_agency = 0;
        $total_commission_order_for_customer = 0;

        $package_weight = 0;

        $point_for_agency = 0;

        $ship_discount_amount = $total_shipping_discount_amount ?? 0;
        $total_before_discount_override = 0;
        $total_after_discount_override = 0;

        //response
        $code = null;
        $success = null;
        $msg_code = null;
        $msg = null;


        $now = Helper::getTimeNowString();
        $line_items_in_time = []; //lưu sp giá hiện tại


        //Tính giảm gia product
        $used_discount = [];

        $total_price_discount_has_edit = 0;
        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        //Tính số dư nếu đã có đơn hàng
        if ($oneCart != null) {
            $balance_collaborator_used_before = $oneCart->balance_collaborator_used_before ?? 0;
            $balance_agency_used_before = $oneCart->balance_agency_used_before ?? 0;
        }


        $isCTVorCus =  CustomerUtils::isRetailCustomer($customer, $request->store->id) || ($customer != null && CollaboratorUtils::isCollaborator($customer->id, $request->store->id));
        foreach ($allCart as $lineItem) {
            //tính khối lượng
            $package_weight = $package_weight + (($lineItem->product->weight <= 0 ? 100 : $lineItem->product->weight) * $lineItem->quantity);

            $is_use_product_retail_step = $lineItem->product->is_product_retail_step && $isCTVorCus;
            $priceBySteps = 0;

            $image_url = null;
            if (count($lineItem->product->images) > 0) {
                $image_url = $lineItem->product->images[0]["image_url"];
            };

            if ($is_use_product_retail_step) {
                $product_retail_step = ProductRetailStep::where('store_id', $request->store->id)
                    ->where('product_id', $lineItem->product->id)
                    ->where('from_quantity', '<=', $lineItem->quantity)
                    ->orderBy('from_quantity', 'desc')
                    ->first();

                if ($product_retail_step) {
                    $priceBySteps = $product_retail_step->price;
                }

                if (!$product_retail_step) {
                    $is_use_product_retail_step = false;
                }
            }



            //Tính lại tiền cho mỗi product line item dua tren distribute
            $lineItem->before_discount_price =    $lineItem->product->price;

            if ($is_use_product_retail_step) {
                $lineItem->before_discount_price = $priceBySteps;
                $lineItem->price_before_override = $priceBySteps;
            }


            $type_product =   ProductUtils::check_type_distribute($lineItem->product);

            if (($lineItem->distributes_selected != null && count($lineItem->distributes_selected ?? []) > 0) || ($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE)) {

                $lineItem->before_discount_price = ProductUtils::get_price_with_distribute(
                    $lineItem->product,
                    $lineItem->distributes_selected[0]->value ?? null,
                    $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
                    'price',
                    $is_order_for_customer,
                    $customer,
                    false
                );

                if ($lineItem->before_discount_price == false) {
                    $disAuto = ProductUtils::auto_choose_distribute($lineItem->product);

                    $lineItem->update([
                        "distributes" => json_encode([
                            [
                                "name" => $disAuto['distribute_name'] ?? "",
                                "sub_element_distributes" =>  $disAuto['sub_element_distribute_name'] ?? "",
                                "value" =>  $disAuto['element_distribute_name'] ?? "",
                            ]
                        ])
                    ]);

                    $lineItem->before_discount_price = ProductUtils::get_price_with_distribute(
                        $lineItem->product,
                        $lineItem->distributes_selected[0]->value ?? null,
                        $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
                        'price',
                        $is_order_for_customer,
                        $customer,
                        false
                    );
                }
            }

            $lineItem->price_before_override = ProductUtils::get_price_with_distribute(
                $lineItem->product,
                $lineItem->distributes_selected[0]->value ?? null,
                $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
                'min_price_before_override',
                $is_order_for_customer,
                $customer
            );

            $type_product =   ProductUtils::check_type_distribute($lineItem->product);
            if (($lineItem->distributes_selected != null && count($lineItem->distributes_selected ?? []) > 0) || ($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE)) {

                $image_url = ProductUtils::get_image_url_distribute(
                    $lineItem->product,
                    $lineItem->distributes_selected[0]->value ?? null,
                    $lineItem->distributes_selected[0]->sub_element_distributes ?? null
                );
            }



            //Chưa giảm giá, voucher và combo
            if ($lineItem->is_bonus == false) {
                if ($lineItem->has_edit_item_price == true) {
                    $total_before_discount += ($lineItem->before_discount_price * $lineItem->quantity);
                    $total_before_discount_override += ($lineItem->price_before_override * $lineItem->quantity);
                } else {
                    $total_before_discount += ($lineItem->before_discount_price * $lineItem->quantity);
                    $total_before_discount_override += ($lineItem->price_before_override * $lineItem->quantity);
                }
            }

            //Tinh giam gia san pham
            $before_discount_price =  $lineItem->before_discount_price;
            $price_before_override =  $lineItem->price_before_override;

            //Có chỉnh giá
            if ($lineItem->has_edit_item_price == true) {

                $total_price_discount_has_edit =  $total_price_discount_has_edit + ($lineItem->quantity * ($lineItem->before_discount_price - $lineItem->item_price));

                array_push(
                    $line_items_in_time,
                    [
                        "id" => $lineItem->product->id,
                        "sku" => $lineItem->product->sku,
                        "quantity" => $lineItem->quantity,
                        "name" => $lineItem->product->name,
                        "image_url" =>  $image_url,
                        'item_price' =>  $lineItem->item_price,
                        'main_price' =>  $lineItem->product->price,
                        'before_discount_price' =>  $lineItem->before_discount_price,
                        'price_before_override' =>  $lineItem->price_before_override,
                        "after_discount" => $lineItem->item_price,
                        "distributes_selected" => $lineItem->distributes_selected,
                        "percent_collaborator" => $lineItem->product->percent_collaborator,
                        "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
                        "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
                        "percent_agency" => $lineItem->product->percent_agency ?? 0,
                        "is_bonus" => $lineItem->is_bonus,
                        'parent_cart_item_ids' => $lineItem->parent_cart_item_ids,
                        "note" =>  $lineItem->note,
                    ],
                );
            } else if ($lineItem->product->product_discount != null) {


                if ($lineItem->is_bonus == false) {
                    $product_item_discount_value = $lineItem->before_discount_price * ($lineItem->product->product_discount['value'] / 100);
                    $product_item_after_discount = $lineItem->before_discount_price * (1 - ($lineItem->product->product_discount['value'] / 100));

                    $product_discount_amount_step =  (int)($product_item_discount_value * $lineItem->quantity);
                    $product_discount_amount =  $product_discount_amount +  $product_discount_amount_step;


                    $product_item_discount_value_override = $lineItem->price_before_override * ($lineItem->product->product_discount['value'] / 100);
                    $product_item_after_discount_override = $lineItem->price_before_override * (1 - ($lineItem->product->product_discount['value'] / 100));

                    $product_discount_amount_step_override =  (int)($product_item_discount_value_override * $lineItem->quantity);
                    $product_discount_amount_override =  $product_discount_amount_override +  $product_discount_amount_step_override;
                } else {
                    $product_item_after_discount =  0;
                    $product_item_after_discount_override = 0;
                    $before_discount_price =  $lineItem->before_discount_price;
                    $price_before_override =  $lineItem->price_before_override;
                }

                $lineItem->update([
                    'before_discount_price' => $before_discount_price,
                    'price_before_override' => $price_before_override,
                    'item_price' =>  $product_item_after_discount
                ]);

                array_push(
                    $used_discount,
                    [
                        "id" => $lineItem->product->id,
                        "quantity" => $lineItem->quantity,
                        "name" => $lineItem->product->name,
                        "image_url" =>  $image_url,
                        'item_price' =>  $product_item_after_discount,
                        'price_before_override' => $product_item_after_discount_override,
                        'main_price' =>  $lineItem->product->price,
                        'before_discount_price' => $before_discount_price,
                        'before_discount_price_override' => $price_before_override,
                        "after_discount" => $product_item_after_discount,
                        "after_discount_override" => $product_item_after_discount_override,
                        "distributes_selected" => $lineItem->distributes_selected,
                        "percent_collaborator" => $lineItem->product->percent_collaborator,
                        "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
                        "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
                        "percent_agency" => $lineItem->product->percent_agency,
                    ],
                );

                array_push(
                    $line_items_in_time,
                    [
                        "id" => $lineItem->product->id,
                        "sku" => $lineItem->product->sku,
                        "quantity" => $lineItem->quantity,
                        "name" => $lineItem->product->name,
                        "image_url" =>  $image_url,
                        'item_price' =>  $product_item_after_discount,
                        "price_before_override" => $product_item_after_discount_override,
                        'main_price' =>  $lineItem->product->price,
                        'before_discount_price' =>    $before_discount_price,
                        'before_discount_price_override' => $price_before_override,
                        "after_discount" => $product_item_after_discount,
                        "after_discount_override" => $product_item_after_discount_override,
                        "distributes_selected" => $lineItem->distributes_selected,
                        "percent_collaborator" => $lineItem->product->percent_collaborator,
                        "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
                        "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
                        "percent_agency" => $lineItem->product->percent_agency,
                        "is_bonus" => $lineItem->is_bonus,
                        'parent_cart_item_ids' => $lineItem->parent_cart_item_ids,
                        "note" =>  $lineItem->note,
                    ],
                );
            } else { // Ko có khuyến mãi thì 2 giá bằng giá trước

                if ($lineItem->is_bonus == false) {

                    $lineItem->update([
                        'before_discount_price' => $before_discount_price,
                        'price_before_override' => $price_before_override,
                        'item_price' =>  $before_discount_price
                    ]);
                } else {
                    $lineItem->update([
                        'before_discount_price' => $before_discount_price,
                        'price_before_override' => $price_before_override,
                        'item_price' => 0
                    ]);
                }


                array_push(
                    $line_items_in_time,
                    [
                        "id" => $lineItem->product->id,
                        "sku" => $lineItem->product->sku,
                        "quantity" => $lineItem->quantity,
                        "name" => $lineItem->product->name,
                        "image_url" =>  $image_url,
                        'item_price' =>  $before_discount_price,
                        'price_before_override' => $price_before_override,
                        'main_price' =>  $lineItem->product->price,
                        'before_discount_price' => $before_discount_price,
                        'before_discount_price_override' => $price_before_override,
                        "after_discount" => $lineItem->item_price,
                        "after_discount_override" => $price_before_override,
                        "distributes_selected" => $lineItem->distributes_selected,
                        "percent_collaborator" => $lineItem->product->percent_collaborator,
                        "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
                        "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
                        "percent_agency" => $lineItem->product->percent_agency,
                        "is_bonus" => $lineItem->is_bonus,
                        'parent_cart_item_ids' => $lineItem->parent_cart_item_ids,
                        "note" =>  $lineItem->note,
                    ],
                );
            }

            /////////Tính chia sẻ cho CTV
            if ($lineItem->is_bonus == false && $lineItem->product->percent_collaborator !== null && $lineItem->product->type_share_collaborator_number == 0 && $lineItem->product->percent_collaborator > 0 && $lineItem->product->percent_collaborator  < 100) {
                $share_collaborator = $share_collaborator + (($lineItem->item_price * ($lineItem->product->percent_collaborator / 100)) * $lineItem->quantity);
            } else
            if ($lineItem->is_bonus == false && $lineItem->product->money_amount_collaborator >= 0  && $lineItem->product->type_share_collaborator_number == 1) {
                $share_collaborator = $share_collaborator +  ($lineItem->product->money_amount_collaborator * $lineItem->quantity);
            }

            /////////Tính chia sẻ cho Agency
            $agency_customer_id = AgencyUtils::isAgencyByCustomerId($request->collaborator_by_customer_id) ? $request->collaborator_by_customer_id : null;
            if ($lineItem->is_bonus == false && $agency_customer_id != null && ($request->customer == null || $request->customer->id != $agency_customer_id)) {

                $agency = AgencyUtils::getAgencyByCustomerId($request->collaborator_by_customer_id);
                if ($agency  != null) {
                    $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                    $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                }
            }


            //Tính tiền hoa hồng nếu đặt hộ
            if ($lineItem->is_bonus == false && $request->is_order_for_customer == true && $customer  != null) {
                $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
                if ($agency  != null) {
                    $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                    $total_commission_order_for_customer = $total_commission_order_for_customer + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                    $share_agency =  $total_commission_order_for_customer;
                    $share_collaborator = 0;
                }
            }



            if ($pointSetting != null) {
                if ($pointSetting->bonus_point_product_to_agency == true) {
                    if ($lineItem->is_bonus == false) {
                        $point_for_agency = $point_for_agency + ($lineItem->product->point_for_agency * $lineItem->quantity);
                    }
                    if ($lineItem->is_bonus == true &&  $pointSetting != null && $pointSetting->bonus_point_bonus_product_to_agency == true) {
                        $point_for_agency = $point_for_agency + ($lineItem->product->point_for_agency * $lineItem->quantity);
                    }
                }
            }
        }


        ///----chốt giảm product
        $total_after_discount = $total_before_discount - $product_discount_amount -  $total_price_discount_has_edit;

        $total_after_discount_override = $total_before_discount_override - $product_discount_amount_override -  $total_price_discount_has_edit;

        /////////////////////////Tạo list product mới để xử lý ko dựa trên giá discount
        $listIdProduct = [];
        foreach ($allCart as $lineItem) {


            if (isset($listIdProduct[$lineItem->product->id])) {

                $after_quantity =  $listIdProduct[$lineItem->product->id]['quantity'];
                $new_quantity = $lineItem->quantity;

                $after_price = $listIdProduct[$lineItem->product->id]['price_or_discount'];
                $new_price =  $lineItem->item_price;

                $avg_price =   ($after_price + $new_price) / 2;
                $total_quantity =    $after_quantity + $new_quantity;

                // if ($new_price >  $after_price) {
                //     $avg_price = $new_price;
                //     $total_quantity = $new_quantity;
                // }


                $listIdProduct[$lineItem->product->id] = [
                    "id"  => $lineItem->product->id,
                    "quantity" => $total_quantity,
                    "price_or_discount" => $avg_price,
                    "is_bonus" => $lineItem->is_bonus
                ];
            } else {
                $listIdProduct[$lineItem->product->id] = [
                    "id"  => $lineItem->product->id,
                    "quantity" => $lineItem->quantity,
                    "price_or_discount" => $lineItem->item_price,
                    "is_bonus" => $lineItem->is_bonus
                ];
            }
        }
        //////////////////////////

        $used_combos = [];

        //Tính giảm giá combo
        $Combos = Combo::where('store_id', $request->store->id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('((combos.amount - combos.used > 0) OR combos.set_limit_amount = false)')
            ->get();


        $CombosRes = [];
        foreach ($Combos as  $ComboItem) {

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $ComboItem->group_customer,
                $ComboItem->agency_type_id,
                $ComboItem->group_type_id,
                $customer,
                $request->store->id,
                $ComboItem->group_customers,
                $ComboItem->agency_types,
                $ComboItem->group_types
            );

            if ($ok_customer) {
                array_push($CombosRes, $ComboItem);
            }
        }

        if (count($CombosRes) > 0) {

            $comboMaxProduct = null;
            $length_product_combo_max = 0;

            foreach ($CombosRes as $combo) {
                $multiplier = null;
                $productValid = 0;
                $lengthProductCombo = count($combo->products_combo);

                foreach ($combo->products_combo as $product_combo) {
                    if (isset($listIdProduct[$product_combo->product->id]) && $listIdProduct[$product_combo->product->id]['is_bonus'] == false) {
                        //kiem tra product va combo quantity > 0
                        if ($product_combo->quantity == 0 || $listIdProduct[$product_combo->product->id]['quantity'] == 0) {
                            break;
                        }

                        $mul = (int)($listIdProduct[$product_combo->product->id]['quantity'] / $product_combo->quantity);

                        if ($multiplier === null) {
                            $multiplier = $mul;
                        }

                        if (($mul < $multiplier && $multiplier != null) == true) {
                            $multiplier = $mul;
                        };

                        $productValid++;
                    } else {
                        break;
                    }
                }

                if ($lengthProductCombo == $productValid && $multiplier != 0) {
                    if ($length_product_combo_max < $lengthProductCombo) {
                        $length_product_combo_max = $lengthProductCombo;
                        $comboMaxProduct = $combo;
                    }
                }
            }

            if ($comboMaxProduct) {
                $totalMoney = 0;

                $multiplier = null; //hệ số nhân combo
                foreach ($comboMaxProduct->products_combo as $product_combo) { //chạy check tất cả sp

                    $totalMoney += $listIdProduct[$product_combo->product->id]['price_or_discount'] * $product_combo->quantity;
                    //tinh ho so nhan moi
                    $mul = (int)($listIdProduct[$product_combo->product->id]['quantity'] / $product_combo->quantity);

                    if ($multiplier === null) {
                        $multiplier = $mul;
                    }

                    if (($mul < $multiplier && $multiplier != null) == true) {
                        $multiplier = $mul;
                    };
                }


                if ($multiplier != 0) {

                    if ($comboMaxProduct->discount_type == 0) {
                        if ($comboMaxProduct->value_discount <=  $totalMoney) {
                            //cong vao gia tien khuyen mai

                            $combo_discount_amount += ($comboMaxProduct->value_discount) * $multiplier;
                        }
                    }
                    if ($comboMaxProduct->discount_type == 1) {

                        $totalDiscounnt = $totalMoney * ($comboMaxProduct->value_discount / 100);
                        if ($totalDiscounnt <= $totalMoney) {
                            $combo_discount_amount += ($totalDiscounnt) * $multiplier;
                        }
                    }

                    array_push($used_combos, [
                        'quantity' => $multiplier,
                        'combo' => $comboMaxProduct
                    ]);
                }

                //-----chốt giảm combo
                $total_after_discount = round($total_after_discount - $combo_discount_amount);
                $total_after_discount_override = round($total_after_discount_override - $combo_discount_amount);
            }
        }


        $used_voucher = null;


        //Tinh giam gia voucher
        $codeVoucher = $code_voucher;

        if (!empty($codeVoucher)) {

            $check_voucher =   VoucherUtils::data_voucher_discount_for_0(
                $codeVoucher,
                $allCart,
                $request,
                $total_after_discount
            );

            if (isset($check_voucher['msg_code'])) {

                if (isset($check_voucher['msg_code'])) {
                    $code =  400;
                    $success =  false;
                    $msg_code =  $check_voucher['msg_code'][0];
                    $msg = $check_voucher['msg_code'][1];
                }
            }

            if (isset($check_voucher['voucher_discount_amount'])) {

                if ($check_voucher['discount_for'] == 1) {
                    $ship_discount_value = $check_voucher['ship_discount_value'] ?? 0;
                    $used_voucher = $check_voucher['used_voucher'];
                    $is_free_ship = $check_voucher['is_free_ship'];

                    if ($is_free_ship == true) {
                        $ship_discount_amount  = $total_shipping_fee;
                    } else {
                        if ($ship_discount_value > $total_shipping_fee) {
                            $ship_discount_amount  = $total_shipping_fee;
                        } else {
                            $ship_discount_amount  =  $ship_discount_value;
                        }
                    }
                } else {
                    $voucher_discount_amount = $check_voucher['voucher_discount_amount'] ?? 0;
                    $used_voucher = $check_voucher['used_voucher'];
                    $total_after_discount = $total_after_discount - $voucher_discount_amount < 0 ? 0 :  $total_after_discount - $voucher_discount_amount;
                    $total_after_discount_override = $total_after_discount_override - $voucher_discount_amount < 0 ? 0 :  $total_after_discount_override - $voucher_discount_amount;
                }
            }
        }
        $total_shipping_fee = $total_shipping_fee  -  $ship_discount_amount > 0 ? $total_shipping_fee  -  $ship_discount_amount : 0;


        //Xử lý trừ số dư CTV đã sử dụng đơn đã đặt và có sử dụng số dư
        $total_after_discount = $total_after_discount - $balance_collaborator_used - $balance_collaborator_used_before < 0 ? 0 :  $total_after_discount - $balance_collaborator_used - $balance_collaborator_used_before;
        $total_after_discount_override = $total_after_discount_override - $balance_collaborator_used - $balance_collaborator_used_before  < 0 ? 0 :  $total_after_discount_override - $balance_collaborator_used - $balance_collaborator_used_before;

        //Xử lý trừ số dư Đại lý đã sử dụng đơn đã đặt và có sử dụng số dư
        $total_after_discount = $total_after_discount - $balance_agency_used - $balance_agency_used_before < 0 ? 0 :  $total_after_discount - $balance_agency_used - $balance_agency_used_before;
        $total_after_discount_override = $total_after_discount_override - $balance_agency_used - $balance_agency_used_before  < 0 ? 0 :  $total_after_discount_override - $balance_agency_used - $balance_agency_used_before;

        //Xử lý trừ xu đã sử dụng đơn đã đặt và có sử dụng xu
        if ($points_amount_used_edit_order > 0) {
            $total_after_discount = $total_after_discount - $points_amount_used_edit_order;

            $total_after_discount_override = $total_after_discount_override - $points_amount_used_edit_order;
        }


        if ($customer != null) {
            //Yêu cầu sử dụng điểm thưởng

            $pointSetting = PointSetting::where(
                'store_id',
                $request->store->id
            )->first();


            if ($pointSetting != null && $pointSetting->allow_use_point_order === true) {

                if ($pointSetting->is_percent_use_max_point == true && $pointSetting->percent_use_max_point >= 0  && $pointSetting->percent_use_max_point <= 100) {

                    $maxMoneyUsePoint =  round($pointSetting->percent_use_max_point * ($total_after_discount / 100));

                    $bonus_points_amount_can_use =  $pointSetting->money_a_point * $customer->points; //tính tổng số point theo tiền



                    if ($bonus_points_amount_can_use >  $maxMoneyUsePoint) {
                        $bonus_points_amount_can_use =  $maxMoneyUsePoint;
                        $total_points_can_use =  round($maxMoneyUsePoint / $pointSetting->money_a_point);
                    } else {
                        $bonus_points_amount_can_use =  $pointSetting->money_a_point * $customer->points; //tính tổng số point theo tiền
                        $total_points_can_use =  round($customer->points);
                    }
                    if ($total_points_can_use == 0) {
                        $bonus_points_amount_can_use = 0;
                    }
                } else {


                    $bonus_points_amount_can_use =  $pointSetting->money_a_point * $customer->points; //tính tổng số point theo tiền
                    $total_points_can_use =   round($customer->points);
                }
            }

            //tính giảm giá điểm thưởng
            $is_use_points = filter_var($is_use_points, FILTER_VALIDATE_BOOLEAN);

            if ($is_use_points === true) {

                if ($bonus_points_amount_can_use > $total_after_discount) {

                    $bonus_points_amount_used  = $total_after_discount;
                    $total_points_used =  round($bonus_points_amount_used / $pointSetting->money_a_point);

                    $total_after_discount = $total_after_discount - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount - $bonus_points_amount_used;
                    $total_after_discount_override = $total_after_discount_override - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount_override - $bonus_points_amount_used;
                } else {
                    $bonus_points_amount_used  = $bonus_points_amount_can_use;
                    $total_points_used  = round($total_points_can_use);
                    $total_after_discount = $total_after_discount - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount - $bonus_points_amount_used;
                    $total_after_discount_override = $total_after_discount_override - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount_override - $bonus_points_amount_used;
                }
            }

            //Yêu cầu sử dụng số dư CTV
            $is_use_balance_collaborator = filter_var($is_use_balance_collaborator, FILTER_VALIDATE_BOOLEAN);
            $collaborator = Collaborator::where('store_id', $request->store->id)
                ->where('customer_id', $customer->id)->first();
            $payAfter = null;
            $payMoneyRequest = 0;
            if ($collaborator != null) {
                $payAfter = PayCollaborator::where('store_id', $request->store->id)
                    ->where('collaborator_id',  $collaborator->id)->where('status', 0)->first();

                if ($payAfter != null) {
                    $payMoneyRequest = $payAfter->money;
                }

                $balance_collaborator_can_use = $collaborator->balance - $payMoneyRequest; // số tiền có thể sử dụng sẽ trừ số tiền request


            }

            if ($is_use_balance_collaborator === true && $collaborator != null) {

                if ($balance_collaborator_can_use > $total_after_discount) {
                    $balance_collaborator_used  = $total_after_discount;

                    $total_after_discount = $total_after_discount - $balance_collaborator_used < 0 ? 0 :  $total_after_discount - $balance_collaborator_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_collaborator_used < 0 ? 0 :  $total_after_discount_override - $balance_collaborator_used;
                } else {
                    $balance_collaborator_used  = $balance_collaborator_can_use;

                    $total_after_discount = $total_after_discount - $balance_collaborator_used < 0 ? 0 :  $total_after_discount - $balance_collaborator_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_collaborator_used < 0 ? 0 :  $total_after_discount_override - $balance_collaborator_used;
                }
                // $balance_collaborator_used = 0; //số dư trong CTV
            }

            //
            //Yêu cầu sử dụng số dư Đại lý
            $is_use_balance_agency = filter_var($is_use_balance_agency, FILTER_VALIDATE_BOOLEAN);
            $agency = Agency::where('store_id', $request->store->id)
                ->where('customer_id', $customer->id)->first();
            $payAfter = null;
            $payMoneyRequest = 0;
            if ($agency != null) {
                $payAfter = PayAgency::where('store_id', $request->store->id)
                    ->where('agency_id',  $agency->id)->where('status', 0)->first();

                if ($payAfter != null) {
                    $payMoneyRequest = $payAfter->money;
                }

                $balance_agency_can_use = $agency->balance - $payMoneyRequest; // số tiền có thể sử dụng sẽ trừ số tiền request


            }

            if ($is_use_balance_agency === true && $agency != null) {

                if ($balance_agency_can_use > $total_after_discount) {
                    $balance_agency_used  = $total_after_discount;

                    $total_after_discount = $total_after_discount - $balance_agency_used < 0 ? 0 :  $total_after_discount - $balance_agency_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_agency_used < 0 ? 0 :  $total_after_discount_override - $balance_agency_used;
                } else {
                    $balance_agency_used  = $balance_agency_can_use;

                    $total_after_discount = $total_after_discount - $balance_agency_used < 0 ? 0 :  $total_after_discount - $balance_agency_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_agency_used < 0 ? 0 :  $total_after_discount_override - $balance_agency_used;
                }
                // $balance_agency_used = 0; //số dư trong CTV
            }
        }

        $config = GeneralSettingController::defaultOfStoreID($request->store->id);
        $enable_vat = $config['enable_vat'] ?? false;
        $percent_vat = $config['percent_vat'] ?: 0;

        $vat = 0;
        $vat_before_override = 0;
        if ($enable_vat == true) {
            $vat =  (int)(($total_after_discount * $percent_vat) / 100);
            $vat_before_override =  (int)(($total_after_discount_override * $percent_vat) / 100);
        }


        //Kiểm tra là đại lý và có thưởng
        $bonusAgency = null;
        if (AgencyUtils::getAgencyByCustomerId($customer == null ? null : $customer->id)) {

            $bonusAgencyConfig = BonusAgency::where('store_id', $request->store->id,)
                ->where('is_end', false)
                ->where('start_time', '<', $now)
                ->where('end_time', '>', $now)
                ->first();
            if ($bonusAgencyConfig != null) {

                $step_bonus = [];
                $total_final2 = $total_before_discount;


                //tìm id thỏa thưởng
                $id_step_active  = null;
                $stepOk = BonusAgencyStep::where('store_id', $request->store->id)->where('threshold', '<=', $total_final2)
                    ->orderBy('threshold', 'desc')->first();

                if ($stepOk != null) {
                    $id_step_active = $stepOk->id;
                }


                $list_step = BonusAgencyStep::where('store_id', $request->store->id)->orderBy('threshold', 'asc')->get();
                foreach ($list_step as $step) {
                    $step = $step->toArray();
                    if ($step['limit'] > 0) {
                        if ($step['id'] == $id_step_active) {
                            $step['active'] = true;
                        } else {
                            $step['active'] = false;
                        };

                        array_push($step_bonus, $step);
                    }
                };

                $bonusAgency =        [
                    "config" =>  $bonusAgencyConfig,
                    "step_bonus" => $step_bonus
                ];
            }
        }

        $total_final = $total_after_discount + $vat + $total_shipping_fee - $discount;
        $total_final_before_override = $total_after_discount_override +  $vat_before_override + $total_shipping_fee - $discount;


        if ($total_final  < 0) {
            if ($oneCart != null) {
                $oneCart = ListCart::where('id', $oneCart->id)->first();
                $oneCart->update([
                    "discount" => 0
                ]);
            }
        }



        return [
            'code' => $code ?? 200,
            'success' => $success ?? true,
            'msg_code' => $msg_code ?? MsgCode::SUCCESS[0],
            'msg' => $msg ?? MsgCode::SUCCESS[1],
            'data' =>  [
                'total_before_discount' => $total_before_discount,
                'balance_collaborator_can_use' => $balance_collaborator_can_use,
                'balance_collaborator_used' => $balance_collaborator_used + $balance_collaborator_used_before,
                'balance_agency_can_use' => $balance_agency_can_use,
                'balance_agency_used' => $balance_agency_used + $balance_agency_used_before,
                'bonus_points_amount_can_use' => $bonus_points_amount_can_use,
                'total_points_can_use' => $total_points_can_use,
                'total_points_used' => $total_points_used,
                'bonus_points_amount_used' => $bonus_points_amount_used,
                'is_use_points' => $is_use_points,
                'is_order_for_customer' => $is_order_for_customer,
                'points_amount_used_edit_order' => $points_amount_used_edit_order,
                'points_total_used_edit_order' => $points_total_used_edit_order,
                'discount' => $discount,
                'ship_discount_amount' => $ship_discount_amount ?? 0,
                'total_shipping_fee' => $total_shipping_fee ?? 0,
                'product_discount_amount' => $product_discount_amount + $total_price_discount_has_edit,
                'total_after_discount' => $total_after_discount,
                'total_final' => $total_final,
                'total_final_before_override' => $total_final_before_override,
                'total_commission_order_for_customer' => $is_order_for_customer ? $total_commission_order_for_customer : 0,
                'used_discount' => $used_discount,
                'combo_discount_amount' => $combo_discount_amount,
                'share_collaborator' => $share_collaborator,
                'share_agency' => $share_agency,
                'point_for_agency' => $point_for_agency,
                'used_combos' => $used_combos,
                'voucher_discount_amount' =>  $voucher_discount_amount,
                'used_voucher' => $used_voucher,
                'line_items' => $allCart,
                'line_items_in_time' => $line_items_in_time,
                'bonus_agency' =>  $bonusAgency,
                'vat' => $vat,
                'package_weight' => $package_weight,
            ]
        ];
    }

    static public function response_info_cart($allCart, $request)
    {

        $data =  CustomerCartController::data_response($allCart, $request);
        return response()->json(
            $data,
            $data['code']
        );
    }

    static public function data_responseV1($allCart, $request, $oneCart = null)
    {
        $code_voucher =  $request->code_voucher;
        $is_use_points = $request->is_use_points;
        $is_use_balance_collaborator = $request->is_use_balance_collaborator;
        $is_use_balance_agency = $request->is_use_balance_agency;
        $discount = $request->discount;
        $total_shipping_fee = $request->total_shipping_fee;
        $is_order_for_customer = $request->is_order_for_customer ?: false;

        //Lưu dữ liệu xu đã sử dụng đơn đã đặt
        $points_amount_used_edit_order = 0;
        $points_total_used_edit_order = 0;
        $total_shipping_discount_amount = 0;
        $customer  = null;
        if ($oneCart != null) {
            $code_voucher = $oneCart->code_voucher ?? "";
            $is_use_points = $oneCart->is_use_points ?? null;
            $is_use_balance_collaborator = $oneCart->is_use_balance_collaborator;
            $is_use_balance_agency = $oneCart->is_use_balance_agency;
            $discount = $oneCart->discount ?? 0;
            $total_shipping_fee = $oneCart->total_shipping_fee ?? 0;
            $total_shipping_discount_amount = $oneCart->ship_discount_amount ?? 0;
            if ($total_shipping_fee != 0) {
                $total_shipping_fee += $total_shipping_discount_amount;
            }

            $points_amount_used_edit_order = $oneCart->points_amount_used_edit_order ?? 0;
            $points_total_used_edit_order = $oneCart->points_total_used_edit_order ?? 0;


            if (!empty($oneCart->customer_id)) {
                $customer = Customer::where('store_id', $request->store->id)->where('id', $oneCart->customer_id)->first();
            } else if (!empty($oneCart->customer_phone)) {
                $customer = Customer::where('store_id', $request->store->id)->where('phone_number', $oneCart->customer_phone)->first();
            }
        }


        if ($customer  == null) {
            $customer = $request->customer;

            if ($customer == null) {
                $request = request();
                $customer = request('customer', $default = null);
            }
        }

        $total_before_discount = 0;

        $balance_collaborator_can_use = 0; //số dư trong CTV
        $balance_collaborator_used = 0; //số dư CTV đã sử dụng
        $balance_collaborator_used_before = 0; //số dư CTV đã sử dụng trước đó

        $balance_agency_can_use = 0; //số dư trong CTV
        $balance_agency_used = 0; //số dư CTV đã sử dụng
        $balance_agency_used_before = 0; // số dư Đại lý đã sử dụng trước đó

        $bonus_points_amount_can_use = 0; //tiền trừ điểm thưởng có thể sử dụng
        $total_points_can_use = 0; // điểm thưởng  có thể sử dụng

        $total_points_used = 0; //điểm đã sử dụng
        $bonus_points_amount_used = 0; //tiền trừ điểm thưởng đã sử dụng

        $combo_discount_amount = 0;
        $product_discount_amount = 0;
        $product_discount_amount_override = 0;
        $voucher_discount_amount = 0;
        $total_after_discount = 0;
        $share_collaborator = 0;
        $share_agency = 0;
        $total_commission_order_for_customer = 0;

        $package_weight = 0;

        $point_for_agency = 0;

        $ship_discount_amount = $total_shipping_discount_amount ?? 0;
        $total_before_discount_override = 0;
        $total_after_discount_override = 0;

        //response
        $code = null;
        $success = null;
        $msg_code = null;
        $msg = null;


        $now = Helper::getTimeNowString();
        $line_items_in_time = []; //lưu sp giá hiện tại


        //Tính giảm gia product
        $used_discount = [];

        $total_price_discount_has_edit = 0;
        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        //Tính số dư nếu đã có đơn hàng
        if ($oneCart != null) {
            $balance_collaborator_used_before = $oneCart->balance_collaborator_used_before ?? 0;
            $balance_agency_used_before = $oneCart->balance_agency_used_before ?? 0;
        }
        $isCTVorCus =  CustomerUtils::isRetailCustomer($customer, $request->store->id) || ($customer != null && CollaboratorUtils::isCollaborator($customer->id, $request->store->id));
        $listIdProduct = [];
        try {
            $response = Http::post('http://localhost:9999/api/data', [
                "allCart" => $allCart,
                "store_id" => $request->store->id,
                "isCTVorCus" => $isCTVorCus,
                "user" => request('user', $default = null),
                "staff" => request('staff', $default = null),
                "customer" => request('customer', $default = null),
                "is_order_for_customer" => $is_order_for_customer,
                "total_before_discount_override" => $total_before_discount_override,
                "total_before_discount" => $total_before_discount,
                "product_discount_amount_override" => $product_discount_amount_override,
                "used_discount" => $used_discount,
                "share_collaborator" => $share_collaborator,
                "collaborator_by_customer_id" => $request->collaborator_by_customer_id,
                "share_agency" => $share_agency,
                "total_commission_order_for_customer" => $total_commission_order_for_customer,
                "pointSetting" => $pointSetting,
                "point_for_agency" => $point_for_agency,
            ]);

            $data = $response->json();

            $package_weight = $data['package_weight'];
            $share_agency = $data['share_agency'];
            $share_collaborator = $data['share_collaborator'];
            $total_commission_order_for_customer = $data['total_commission_order_for_customer'];
            $total_before_discount = $data['total_before_discount'];
            $total_before_discount_override = $data['total_before_discount_override'];
            $product_discount_amount = $data['product_discount_amount'];
            $product_discount_amount_override = $data['product_discount_amount_override'];
            $point_for_agency = $data['point_for_agency'];
            $total_price_discount_has_edit = $data['total_price_discount_has_edit'];
            $used_discount = $data['used_discount'];
            $line_items_in_time = $data['line_items_in_time'];
            $listIdProduct = $data['listIdProduct'];
            $allCart = $data['allCart'];
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data from Node.js API'], 500);
        }

        // function searchObjectInArray($searchObject, $array)
        // {
        //     foreach ($array as $item) {
        //         if (is_array($item) && count(array_intersect_assoc($item, $searchObject)) == count($searchObject)) {
        //             return true;
        //         }
        //     }
        //     return false;
        // }

        // function objectToArray($object)
        // {
        //     $array = [];
        //     foreach ($object as $key => $value) {
        //         if (is_object($value)) {
        //             $array[$key] = objectToArray($value);
        //         } else {
        //             $array[$key] = $value;
        //         }
        //     }
        //     return $array;
        // }

        // dd($allCart[0]->product->distributes[0]->element_distributes);
        // return;
        // $a = $lineItem->distributes_selected != null;
        // $a = (object) [];
        // dd($request->distributeArray);
        // dd(searchObjectInArray(objectToArray($a), $request->distributeArray));

        // foreach ($allCart as $lineItem) {
        //     $objectItem = (object) [];
        //     if ($lineItem->distributes_selected != null && count($lineItem->distributes_selected) > 0) {

        //         $objectItem = (object) $lineItem->distributes_selected;
        //         $objectItem->{'0'}->id = $lineItem->product->id;
        //     }

        //     if (
        //         !in_array($lineItem->product->id, $request->productIdCaculated)
        //         || (in_array($lineItem->product->id, $request->productIdCaculated) && searchObjectInArray(objectToArray($objectItem), $request->distributeArray))
        //     ) {
        //         $package_weight = $package_weight + (($lineItem->product->weight <= 0 ? 100 : $lineItem->product->weight) * $lineItem->quantity);

        //         $is_use_product_retail_step = $lineItem->product->is_product_retail_step && $isCTVorCus;
        //         $priceBySteps = 0;

        //         $image_url = null;
        //         if (count($lineItem->product->images) > 0) {
        //             $image_url = $lineItem->product->images[0]["image_url"];
        //         };

        //         if ($is_use_product_retail_step) {
        //             $product_retail_step = ProductRetailStep::where('store_id', $request->store->id)
        //                 ->where('product_id', $lineItem->product->id)
        //                 ->where('from_quantity', '<=', $lineItem->quantity)
        //                 ->orderBy('from_quantity', 'desc')
        //                 ->first();


        //             if ($product_retail_step) {
        //                 $priceBySteps = $product_retail_step->price;
        //             }

        //             if (!$product_retail_step) {
        //                 $is_use_product_retail_step = false;
        //             }
        //         }



        //         //Tính lại tiền cho mỗi product line item dua tren distribute
        //         $lineItem->before_discount_price =    $lineItem->product->price;

        //         if ($is_use_product_retail_step) {
        //             $lineItem->before_discount_price = $priceBySteps;
        //             $lineItem->price_before_override = $priceBySteps;
        //         }


        //         $type_product =   ProductUtils::check_type_distribute($lineItem->product);

        //         if (($lineItem->distributes_selected != null && count($lineItem->distributes_selected ?? []) > 0) || ($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE)) {

        //             $lineItem->before_discount_price = ProductUtils::get_price_with_distribute(
        //                 $lineItem->product,
        //                 $lineItem->distributes_selected[0]->value ?? null,
        //                 $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
        //                 'price',
        //                 $is_order_for_customer,
        //                 $customer,
        //                 false
        //             );

        //             if ($lineItem->before_discount_price == false) {
        //                 $disAuto = ProductUtils::auto_choose_distribute($lineItem->product);

        //                 $lineItem->update([
        //                     "distributes" => json_encode([
        //                         [
        //                             "name" => $disAuto['distribute_name'] ?? "",
        //                             "sub_element_distributes" =>  $disAuto['sub_element_distribute_name'] ?? "",
        //                             "value" =>  $disAuto['element_distribute_name'] ?? "",
        //                         ]
        //                     ])
        //                 ]);

        //                 $lineItem->before_discount_price = ProductUtils::get_price_with_distribute(
        //                     $lineItem->product,
        //                     $lineItem->distributes_selected[0]->value ?? null,
        //                     $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
        //                     'price',
        //                     $is_order_for_customer,
        //                     $customer,
        //                     false
        //                 );
        //             }
        //         }

        //         $lineItem->before_discount_price = ProductUtils::get_price_with_distribute(
        //             $lineItem->product,
        //             $lineItem->distributes_selected[0]->value ?? null,
        //             $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
        //             'price',
        //             $is_order_for_customer,
        //             $customer,
        //             false
        //         );

        //         $lineItem->price_before_override = ProductUtils::get_price_with_distribute(
        //             $lineItem->product,
        //             $lineItem->distributes_selected[0]->value ?? null,
        //             $lineItem->distributes_selected[0]->sub_element_distributes ?? null,
        //             'min_price_before_override',
        //             $is_order_for_customer,
        //             $customer
        //         );

        //         // $type_product =   ProductUtils::check_type_distribute($lineItem->product);
        //         if (($lineItem->distributes_selected != null && count($lineItem->distributes_selected ?? []) > 0) || ($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE)) {

        //             $image_url = ProductUtils::get_image_url_distribute(
        //                 $lineItem->product,
        //                 $lineItem->distributes_selected[0]->value ?? null,
        //                 $lineItem->distributes_selected[0]->sub_element_distributes ?? null
        //             );
        //         }
        //     } else {
        //         $package_weight = $package_weight + (($lineItem->product->weight <= 0 ? 100 : $lineItem->product->weight) * $lineItem->quantity);

        //         $is_use_product_retail_step = $lineItem->product->is_product_retail_step && $isCTVorCus;
        //         $priceBySteps = 0;

        //         $image_url = null;
        //         if (count($lineItem->product->images) > 0) {
        //             $image_url = $lineItem->product->images[0]["image_url"];
        //         };

        //         if ($is_use_product_retail_step) {
        //             $product_retail_step = ProductRetailStep::where('store_id', $request->store->id)
        //                 ->where('product_id', $lineItem->product->id)
        //                 ->where('from_quantity', '<=', $lineItem->quantity)
        //                 ->orderBy('from_quantity', 'desc')
        //                 ->first();


        //             if ($product_retail_step) {
        //                 $priceBySteps = $product_retail_step->price;
        //             }

        //             if (!$product_retail_step) {
        //                 $is_use_product_retail_step = false;
        //             }
        //         }



        //         //Tính lại tiền cho mỗi product line item dua tren distribute
        //         $lineItem->before_discount_price =    $lineItem->product->price;

        //         if ($is_use_product_retail_step) {
        //             $lineItem->before_discount_price = $priceBySteps;
        //             $lineItem->price_before_override = $priceBySteps;
        //         }


        //         $type_product =   ProductUtils::check_type_distribute($lineItem->product);

        //         $oldData = null;
        //         foreach ($request->oldResponseOfCartInfo["line_items_in_time"] as $item) {
        //             if ($item["id"] == $lineItem->product->id) {
        //                 $oldData = $item;
        //             }
        //         }
        //         if (($lineItem->distributes_selected != null && count($lineItem->distributes_selected ?? []) > 0) || ($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE)) {
        //             $lineItem->before_discount_price = $oldData["before_discount_price"];
        //         }

        //         $lineItem->price_before_override = $oldData["price_before_override"];

        //         // $type_product =   ProductUtils::check_type_distribute($lineItem->product);
        //         if (($lineItem->distributes_selected != null && count($lineItem->distributes_selected ?? []) > 0) || ($type_product  == ProductUtils::HAS_SUB || $type_product  == ProductUtils::HAS_ELE)) {

        //             $image_url = $oldData["image_url"];
        //         }
        //     }
        //     //tính khối lượng




        //     //Chưa giảm giá, voucher và combo
        //     if ($lineItem->is_bonus == false) {
        //         // if ($lineItem->has_edit_item_price == true) {
        //         //     $total_before_discount += ($lineItem->before_discount_price * $lineItem->quantity);
        //         //     $total_before_discount_override += ($lineItem->price_before_override * $lineItem->quantity);
        //         // } else {
        //         //     $total_before_discount += ($lineItem->before_discount_price * $lineItem->quantity);
        //         //     $total_before_discount_override += ($lineItem->price_before_override * $lineItem->quantity);
        //         // }
        //         $total_before_discount += ($lineItem->before_discount_price * $lineItem->quantity);
        //         $total_before_discount_override += ($lineItem->price_before_override * $lineItem->quantity);
        //     }

        //     //Tinh giam gia san pham
        //     $before_discount_price =  $lineItem->before_discount_price;
        //     $price_before_override =  $lineItem->price_before_override;

        //     //Có chỉnh giá
        //     if ($lineItem->has_edit_item_price == true) {

        //         $total_price_discount_has_edit =  $total_price_discount_has_edit + ($lineItem->quantity * ($lineItem->before_discount_price - $lineItem->item_price));

        //         array_push(
        //             $line_items_in_time,
        //             [
        //                 "id" => $lineItem->product->id,
        //                 "sku" => $lineItem->product->sku,
        //                 "quantity" => $lineItem->quantity,
        //                 "name" => $lineItem->product->name,
        //                 "image_url" =>  $image_url,
        //                 'item_price' =>  $lineItem->item_price,
        //                 'main_price' =>  $lineItem->product->price,
        //                 'before_discount_price' =>  $lineItem->before_discount_price,
        //                 'price_before_override' =>  $lineItem->price_before_override,
        //                 "after_discount" => $lineItem->item_price,
        //                 "distributes_selected" => $lineItem->distributes_selected,
        //                 "percent_collaborator" => $lineItem->product->percent_collaborator,
        //                 "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
        //                 "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
        //                 "percent_agency" => $lineItem->product->percent_agency ?? 0,
        //                 "is_bonus" => $lineItem->is_bonus,
        //                 'parent_cart_item_ids' => $lineItem->parent_cart_item_ids,
        //                 "note" =>  $lineItem->note,
        //             ],
        //         );
        //     } else if ($lineItem->product->product_discount != null) {


        //         if ($lineItem->is_bonus == false) {
        //             $product_item_discount_value = $lineItem->before_discount_price * ($lineItem->product->product_discount['value'] / 100);
        //             $product_item_after_discount = $lineItem->before_discount_price * (1 - ($lineItem->product->product_discount['value'] / 100));

        //             $product_discount_amount_step =  (int)($product_item_discount_value * $lineItem->quantity);
        //             $product_discount_amount =  $product_discount_amount +  $product_discount_amount_step;


        //             $product_item_discount_value_override = $lineItem->price_before_override * ($lineItem->product->product_discount['value'] / 100);
        //             $product_item_after_discount_override = $lineItem->price_before_override * (1 - ($lineItem->product->product_discount['value'] / 100));

        //             $product_discount_amount_step_override =  (int)($product_item_discount_value_override * $lineItem->quantity);
        //             $product_discount_amount_override =  $product_discount_amount_override +  $product_discount_amount_step_override;
        //         } else {
        //             $product_item_after_discount =  0;
        //             $product_item_after_discount_override = 0;
        //             $before_discount_price =  $lineItem->before_discount_price;
        //             $price_before_override =  $lineItem->price_before_override;
        //         }

        //         $lineItem->update([
        //             'before_discount_price' => $before_discount_price,
        //             'price_before_override' => $price_before_override,
        //             'item_price' =>  $product_item_after_discount
        //         ]);

        //         array_push(
        //             $used_discount,
        //             [
        //                 "id" => $lineItem->product->id,
        //                 "quantity" => $lineItem->quantity,
        //                 "name" => $lineItem->product->name,
        //                 "image_url" =>  $image_url,
        //                 'item_price' =>  $product_item_after_discount,
        //                 'price_before_override' => $product_item_after_discount_override,
        //                 'main_price' =>  $lineItem->product->price,
        //                 'before_discount_price' => $before_discount_price,
        //                 'before_discount_price_override' => $price_before_override,
        //                 "after_discount" => $product_item_after_discount,
        //                 "after_discount_override" => $product_item_after_discount_override,
        //                 "distributes_selected" => $lineItem->distributes_selected,
        //                 "percent_collaborator" => $lineItem->product->percent_collaborator,
        //                 "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
        //                 "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
        //                 "percent_agency" => $lineItem->product->percent_agency,
        //             ],
        //         );

        //         array_push(
        //             $line_items_in_time,
        //             [
        //                 "id" => $lineItem->product->id,
        //                 "sku" => $lineItem->product->sku,
        //                 "quantity" => $lineItem->quantity,
        //                 "name" => $lineItem->product->name,
        //                 "image_url" =>  $image_url,
        //                 'item_price' =>  $product_item_after_discount,
        //                 "price_before_override" => $product_item_after_discount_override,
        //                 'main_price' =>  $lineItem->product->price,
        //                 'before_discount_price' =>    $before_discount_price,
        //                 'before_discount_price_override' => $price_before_override,
        //                 "after_discount" => $product_item_after_discount,
        //                 "after_discount_override" => $product_item_after_discount_override,
        //                 "distributes_selected" => $lineItem->distributes_selected,
        //                 "percent_collaborator" => $lineItem->product->percent_collaborator,
        //                 "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
        //                 "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
        //                 "percent_agency" => $lineItem->product->percent_agency,
        //                 "is_bonus" => $lineItem->is_bonus,
        //                 'parent_cart_item_ids' => $lineItem->parent_cart_item_ids,
        //                 "note" =>  $lineItem->note,
        //             ],
        //         );
        //     } else { // Ko có khuyến mãi thì 2 giá bằng giá trước

        //         if ($lineItem->is_bonus == false) {

        //             $lineItem->update([
        //                 'before_discount_price' => $before_discount_price,
        //                 'price_before_override' => $price_before_override,
        //                 'item_price' =>  $before_discount_price
        //             ]);
        //         } else {
        //             $lineItem->update([
        //                 'before_discount_price' => $before_discount_price,
        //                 'price_before_override' => $price_before_override,
        //                 'item_price' => 0
        //             ]);
        //         }


        //         array_push(
        //             $line_items_in_time,
        //             [
        //                 "id" => $lineItem->product->id,
        //                 "sku" => $lineItem->product->sku,
        //                 "quantity" => $lineItem->quantity,
        //                 "name" => $lineItem->product->name,
        //                 "image_url" =>  $image_url,
        //                 'item_price' =>  $before_discount_price,
        //                 'price_before_override' => $price_before_override,
        //                 'main_price' =>  $lineItem->product->price,
        //                 'before_discount_price' => $before_discount_price,
        //                 'before_discount_price_override' => $price_before_override,
        //                 "after_discount" => $lineItem->item_price,
        //                 "after_discount_override" => $price_before_override,
        //                 "distributes_selected" => $lineItem->distributes_selected,
        //                 "percent_collaborator" => $lineItem->product->percent_collaborator,
        //                 "type_share_collaborator_number" => $lineItem->product->type_share_collaborator_number,
        //                 "money_amount_collaborator" => $lineItem->product->money_amount_collaborator,
        //                 "percent_agency" => $lineItem->product->percent_agency,
        //                 "is_bonus" => $lineItem->is_bonus,
        //                 'parent_cart_item_ids' => $lineItem->parent_cart_item_ids,
        //                 "note" =>  $lineItem->note,
        //             ],
        //         );
        //     }

        //     /////////Tính chia sẻ cho CTV
        //     if (
        //         $lineItem->is_bonus == false
        //         && $lineItem->product->percent_collaborator !== null
        //         && $lineItem->product->type_share_collaborator_number == 0
        //         && $lineItem->product->percent_collaborator > 0
        //         && $lineItem->product->percent_collaborator  < 100
        //     ) {
        //         $share_collaborator = $share_collaborator + (($lineItem->item_price * ($lineItem->product->percent_collaborator / 100)) * $lineItem->quantity);
        //     } else
        //     if ($lineItem->is_bonus == false && $lineItem->product->money_amount_collaborator >= 0  && $lineItem->product->type_share_collaborator_number == 1) {
        //         $share_collaborator = $share_collaborator +  ($lineItem->product->money_amount_collaborator * $lineItem->quantity);
        //     }

        //     /////////Tính chia sẻ cho Agency
        //     $agency_customer_id = AgencyUtils::isAgencyByCustomerId($request->collaborator_by_customer_id) ? $request->collaborator_by_customer_id : null;
        //     if ($lineItem->is_bonus == false && $agency_customer_id != null && ($request->customer == null || $request->customer->id != $agency_customer_id)) {

        //         $agency = AgencyUtils::getAgencyByCustomerId($request->collaborator_by_customer_id);
        //         if ($agency  != null) {
        //             $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
        //             $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
        //         }
        //     }


        //     //Tính tiền hoa hồng nếu đặt hộ
        //     if ($lineItem->is_bonus == false && $request->is_order_for_customer == true && $customer  != null) {
        //         $agency = AgencyUtils::getAgencyByCustomerId($customer->id);
        //         if ($agency  != null) {
        //             $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
        //             $total_commission_order_for_customer = $total_commission_order_for_customer + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
        //             $share_agency =  $total_commission_order_for_customer;
        //             $share_collaborator = 0;
        //         }
        //     }



        //     if ($pointSetting != null) {
        //         if ($pointSetting->bonus_point_product_to_agency == true) {
        //             if ($lineItem->is_bonus == false) {
        //                 $point_for_agency = $point_for_agency + ($lineItem->product->point_for_agency * $lineItem->quantity);
        //             }
        //             if ($lineItem->is_bonus == true &&  $pointSetting != null && $pointSetting->bonus_point_bonus_product_to_agency == true) {
        //                 $point_for_agency = $point_for_agency + ($lineItem->product->point_for_agency * $lineItem->quantity);
        //             }
        //         }
        //     }

        //     if (isset($listIdProduct[$lineItem->product->id])) {

        //         $after_quantity =  $listIdProduct[$lineItem->product->id]['quantity'];
        //         $new_quantity = $lineItem->quantity;

        //         $after_price = $listIdProduct[$lineItem->product->id]['price_or_discount'];
        //         $new_price =  $lineItem->item_price;

        //         $avg_price =   ($after_price + $new_price) / 2;
        //         $total_quantity =    $after_quantity + $new_quantity;

        //         // if ($new_price >  $after_price) {
        //         //     $avg_price = $new_price;
        //         //     $total_quantity = $new_quantity;
        //         // }


        //         $listIdProduct[$lineItem->product->id] = [
        //             "id"  => $lineItem->product->id,
        //             "quantity" => $total_quantity,
        //             "price_or_discount" => $avg_price,
        //             "is_bonus" => $lineItem->is_bonus
        //         ];
        //     } else {
        //         $listIdProduct[$lineItem->product->id] = [
        //             "id"  => $lineItem->product->id,
        //             "quantity" => $lineItem->quantity,
        //             "price_or_discount" => $lineItem->item_price,
        //             "is_bonus" => $lineItem->is_bonus
        //         ];
        //     }
        // }


        ///----chốt giảm product
        $total_after_discount = $total_before_discount - $product_discount_amount -  $total_price_discount_has_edit;

        $total_after_discount_override = $total_before_discount_override - $product_discount_amount_override -  $total_price_discount_has_edit;

        /////////////////////////Tạo list product mới để xử lý ko dựa trên giá discount

        $used_combos = [];

        //Tính giảm giá combo
        $Combos = Combo::where('store_id', $request->store->id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('((combos.amount - combos.used > 0) OR combos.set_limit_amount = false)')
            ->get();


        $CombosRes = [];
        foreach ($Combos as  $ComboItem) {

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $ComboItem->group_customer,
                $ComboItem->agency_type_id,
                $ComboItem->group_type_id,
                $customer,
                $request->store->id,
                $ComboItem->group_customers,
                $ComboItem->agency_types,
                $ComboItem->group_types
            );

            if ($ok_customer) {
                array_push($CombosRes, $ComboItem);
            }
        }

        if (count($CombosRes) > 0) {

            $comboMaxProduct = null;
            $length_product_combo_max = 0;

            foreach ($CombosRes as $combo) {
                $multiplier = null;
                $productValid = 0;
                $lengthProductCombo = count($combo->products_combo);

                foreach ($combo->products_combo as $product_combo) {
                    if (isset($listIdProduct[$product_combo->product->id]) && $listIdProduct[$product_combo->product->id]['is_bonus'] == false) {
                        //kiem tra product va combo quantity > 0
                        if ($product_combo->quantity == 0 || $listIdProduct[$product_combo->product->id]['quantity'] == 0) {
                            break;
                        }

                        $mul = (int)($listIdProduct[$product_combo->product->id]['quantity'] / $product_combo->quantity);

                        if ($multiplier === null) {
                            $multiplier = $mul;
                        }

                        if (($mul < $multiplier && $multiplier != null) == true) {
                            $multiplier = $mul;
                        };

                        $productValid++;
                    } else {
                        break;
                    }
                }

                if ($lengthProductCombo == $productValid && $multiplier != 0) {
                    if ($length_product_combo_max < $lengthProductCombo) {
                        $length_product_combo_max = $lengthProductCombo;
                        $comboMaxProduct = $combo;
                    }
                }
            }

            if ($comboMaxProduct) {
                $totalMoney = 0;

                $multiplier = null; //hệ số nhân combo
                foreach ($comboMaxProduct->products_combo as $product_combo) { //chạy check tất cả sp

                    $totalMoney += $listIdProduct[$product_combo->product->id]['price_or_discount'] * $product_combo->quantity;
                    //tinh ho so nhan moi
                    $mul = (int)($listIdProduct[$product_combo->product->id]['quantity'] / $product_combo->quantity);

                    if ($multiplier === null) {
                        $multiplier = $mul;
                    }

                    if (($mul < $multiplier && $multiplier != null) == true) {
                        $multiplier = $mul;
                    };
                }


                if ($multiplier != 0) {

                    if ($comboMaxProduct->discount_type == 0) {
                        if ($comboMaxProduct->value_discount <=  $totalMoney) {
                            //cong vao gia tien khuyen mai

                            $combo_discount_amount += ($comboMaxProduct->value_discount) * $multiplier;
                        }
                    }
                    if ($comboMaxProduct->discount_type == 1) {

                        $totalDiscounnt = $totalMoney * ($comboMaxProduct->value_discount / 100);
                        if ($totalDiscounnt <= $totalMoney) {
                            $combo_discount_amount += ($totalDiscounnt) * $multiplier;
                        }
                    }

                    array_push($used_combos, [
                        'quantity' => $multiplier,
                        'combo' => $comboMaxProduct
                    ]);
                }

                //-----chốt giảm combo
                $total_after_discount = round($total_after_discount - $combo_discount_amount);
                $total_after_discount_override = round($total_after_discount_override - $combo_discount_amount);
            }
        }


        $used_voucher = null;


        //Tinh giam gia voucher
        $codeVoucher = $code_voucher;

        if (!empty($codeVoucher)) {
            $check_voucher =   VoucherUtils::data_voucher_discount_for_0V1(
                $codeVoucher,
                $allCart,
                $request,
                $total_after_discount
            );

            if (isset($check_voucher['msg_code'])) {

                if (isset($check_voucher['msg_code'])) {
                    $code =  400;
                    $success =  false;
                    $msg_code =  $check_voucher['msg_code'][0];
                    $msg = $check_voucher['msg_code'][1];
                }
            }

            if (isset($check_voucher['voucher_discount_amount'])) {

                if ($check_voucher['discount_for'] == 1) {
                    $ship_discount_value = $check_voucher['ship_discount_value'] ?? 0;
                    $used_voucher = $check_voucher['used_voucher'];
                    $is_free_ship = $check_voucher['is_free_ship'];

                    if ($is_free_ship == true) {
                        $ship_discount_amount  = $total_shipping_fee;
                    } else {
                        if ($ship_discount_value > $total_shipping_fee) {
                            $ship_discount_amount  = $total_shipping_fee;
                        } else {
                            $ship_discount_amount  =  $ship_discount_value;
                        }
                    }
                } else {
                    $voucher_discount_amount = $check_voucher['voucher_discount_amount'] ?? 0;
                    $used_voucher = $check_voucher['used_voucher'];
                    $total_after_discount = $total_after_discount - $voucher_discount_amount < 0 ? 0 :  $total_after_discount - $voucher_discount_amount;
                    $total_after_discount_override = $total_after_discount_override - $voucher_discount_amount < 0 ? 0 :  $total_after_discount_override - $voucher_discount_amount;
                }
            }
        }
        $total_shipping_fee = $total_shipping_fee  -  $ship_discount_amount > 0 ? $total_shipping_fee  -  $ship_discount_amount : 0;


        //Xử lý trừ số dư CTV đã sử dụng đơn đã đặt và có sử dụng số dư
        $total_after_discount = $total_after_discount - $balance_collaborator_used - $balance_collaborator_used_before < 0 ? 0 :  $total_after_discount - $balance_collaborator_used - $balance_collaborator_used_before;
        $total_after_discount_override = $total_after_discount_override - $balance_collaborator_used - $balance_collaborator_used_before  < 0 ? 0 :  $total_after_discount_override - $balance_collaborator_used - $balance_collaborator_used_before;

        //Xử lý trừ số dư Đại lý đã sử dụng đơn đã đặt và có sử dụng số dư
        $total_after_discount = $total_after_discount - $balance_agency_used - $balance_agency_used_before < 0 ? 0 :  $total_after_discount - $balance_agency_used - $balance_agency_used_before;
        $total_after_discount_override = $total_after_discount_override - $balance_agency_used - $balance_agency_used_before  < 0 ? 0 :  $total_after_discount_override - $balance_agency_used - $balance_agency_used_before;

        //Xử lý trừ xu đã sử dụng đơn đã đặt và có sử dụng xu
        if ($points_amount_used_edit_order > 0) {
            $total_after_discount = $total_after_discount - $points_amount_used_edit_order;

            $total_after_discount_override = $total_after_discount_override - $points_amount_used_edit_order;
        }


        if ($customer != null) {
            //Yêu cầu sử dụng điểm thưởng

            $pointSetting = PointSetting::where(
                'store_id',
                $request->store->id
            )->first();


            if ($pointSetting != null && $pointSetting->allow_use_point_order === true) {

                if ($pointSetting->is_percent_use_max_point == true && $pointSetting->percent_use_max_point >= 0  && $pointSetting->percent_use_max_point <= 100) {

                    $maxMoneyUsePoint =  round($pointSetting->percent_use_max_point * ($total_after_discount / 100));

                    $bonus_points_amount_can_use =  $pointSetting->money_a_point * $customer->points; //tính tổng số point theo tiền



                    if ($bonus_points_amount_can_use >  $maxMoneyUsePoint) {
                        $bonus_points_amount_can_use =  $maxMoneyUsePoint;
                        $total_points_can_use =  round($maxMoneyUsePoint / $pointSetting->money_a_point);
                    } else {
                        $bonus_points_amount_can_use =  $pointSetting->money_a_point * $customer->points; //tính tổng số point theo tiền
                        $total_points_can_use =  round($customer->points);
                    }
                    if ($total_points_can_use == 0) {
                        $bonus_points_amount_can_use = 0;
                    }
                } else {


                    $bonus_points_amount_can_use =  $pointSetting->money_a_point * $customer->points; //tính tổng số point theo tiền
                    $total_points_can_use =   round($customer->points);
                }
            }

            //tính giảm giá điểm thưởng
            $is_use_points = filter_var($is_use_points, FILTER_VALIDATE_BOOLEAN);

            if ($is_use_points === true) {

                if ($bonus_points_amount_can_use > $total_after_discount) {

                    $bonus_points_amount_used  = $total_after_discount;
                    $total_points_used =  round($bonus_points_amount_used / $pointSetting->money_a_point);

                    $total_after_discount = $total_after_discount - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount - $bonus_points_amount_used;
                    $total_after_discount_override = $total_after_discount_override - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount_override - $bonus_points_amount_used;
                } else {
                    $bonus_points_amount_used  = $bonus_points_amount_can_use;
                    $total_points_used  = round($total_points_can_use);
                    $total_after_discount = $total_after_discount - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount - $bonus_points_amount_used;
                    $total_after_discount_override = $total_after_discount_override - $bonus_points_amount_used < 0 ? 0 :  $total_after_discount_override - $bonus_points_amount_used;
                }
            }

            //Yêu cầu sử dụng số dư CTV
            $is_use_balance_collaborator = filter_var($is_use_balance_collaborator, FILTER_VALIDATE_BOOLEAN);
            $collaborator = Collaborator::where('store_id', $request->store->id)
                ->where('customer_id', $customer->id)->first();
            $payAfter = null;
            $payMoneyRequest = 0;
            if ($collaborator != null) {
                $payAfter = PayCollaborator::where('store_id', $request->store->id)
                    ->where('collaborator_id',  $collaborator->id)->where('status', 0)->first();

                if ($payAfter != null) {
                    $payMoneyRequest = $payAfter->money;
                }

                $balance_collaborator_can_use = $collaborator->balance - $payMoneyRequest; // số tiền có thể sử dụng sẽ trừ số tiền request


            }

            if ($is_use_balance_collaborator === true && $collaborator != null) {

                if ($balance_collaborator_can_use > $total_after_discount) {
                    $balance_collaborator_used  = $total_after_discount;

                    $total_after_discount = $total_after_discount - $balance_collaborator_used < 0 ? 0 :  $total_after_discount - $balance_collaborator_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_collaborator_used < 0 ? 0 :  $total_after_discount_override - $balance_collaborator_used;
                } else {
                    $balance_collaborator_used  = $balance_collaborator_can_use;

                    $total_after_discount = $total_after_discount - $balance_collaborator_used < 0 ? 0 :  $total_after_discount - $balance_collaborator_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_collaborator_used < 0 ? 0 :  $total_after_discount_override - $balance_collaborator_used;
                }
                // $balance_collaborator_used = 0; //số dư trong CTV
            }

            //
            //Yêu cầu sử dụng số dư Đại lý
            $is_use_balance_agency = filter_var($is_use_balance_agency, FILTER_VALIDATE_BOOLEAN);
            $agency = Agency::where('store_id', $request->store->id)
                ->where('customer_id', $customer->id)->first();
            $payAfter = null;
            $payMoneyRequest = 0;
            if ($agency != null) {
                $payAfter = PayAgency::where('store_id', $request->store->id)
                    ->where('agency_id',  $agency->id)->where('status', 0)->first();

                if ($payAfter != null) {
                    $payMoneyRequest = $payAfter->money;
                }

                $balance_agency_can_use = $agency->balance - $payMoneyRequest; // số tiền có thể sử dụng sẽ trừ số tiền request


            }

            if ($is_use_balance_agency === true && $agency != null) {

                if ($balance_agency_can_use > $total_after_discount) {
                    $balance_agency_used  = $total_after_discount;

                    $total_after_discount = $total_after_discount - $balance_agency_used < 0 ? 0 :  $total_after_discount - $balance_agency_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_agency_used < 0 ? 0 :  $total_after_discount_override - $balance_agency_used;
                } else {
                    $balance_agency_used  = $balance_agency_can_use;

                    $total_after_discount = $total_after_discount - $balance_agency_used < 0 ? 0 :  $total_after_discount - $balance_agency_used;
                    $total_after_discount_override = $total_after_discount_override - $balance_agency_used < 0 ? 0 :  $total_after_discount_override - $balance_agency_used;
                }
                // $balance_agency_used = 0; //số dư trong CTV
            }
        }

        $config = GeneralSettingController::defaultOfStoreID($request->store->id);
        $enable_vat = $config['enable_vat'] ?? false;
        $percent_vat = $config['percent_vat'] ?: 0;

        $vat = 0;
        $vat_before_override = 0;
        if ($enable_vat == true) {
            $vat =  (int)(($total_after_discount * $percent_vat) / 100);
            $vat_before_override =  (int)(($total_after_discount_override * $percent_vat) / 100);
        }


        //Kiểm tra là đại lý và có thưởng
        $bonusAgency = null;
        if (AgencyUtils::getAgencyByCustomerId($customer == null ? null : $customer->id)) {

            $bonusAgencyConfig = BonusAgency::where('store_id', $request->store->id,)
                ->where('is_end', false)
                ->where('start_time', '<', $now)
                ->where('end_time', '>', $now)
                ->first();
            if ($bonusAgencyConfig != null) {

                $step_bonus = [];
                $total_final2 = $total_before_discount;


                //tìm id thỏa thưởng
                $id_step_active  = null;
                $stepOk = BonusAgencyStep::where('store_id', $request->store->id)->where('threshold', '<=', $total_final2)
                    ->orderBy('threshold', 'desc')->first();

                if ($stepOk != null) {
                    $id_step_active = $stepOk->id;
                }


                $list_step = BonusAgencyStep::where('store_id', $request->store->id)->orderBy('threshold', 'asc')->get();
                foreach ($list_step as $step) {
                    $step = $step->toArray();
                    if ($step['limit'] > 0) {
                        if ($step['id'] == $id_step_active) {
                            $step['active'] = true;
                        } else {
                            $step['active'] = false;
                        };

                        array_push($step_bonus, $step);
                    }
                };

                $bonusAgency =        [
                    "config" =>  $bonusAgencyConfig,
                    "step_bonus" => $step_bonus
                ];
            }
        }

        $total_final = $total_after_discount + $vat + $total_shipping_fee - $discount;
        $total_final_before_override = $total_after_discount_override +  $vat_before_override + $total_shipping_fee - $discount;


        if ($total_final  < 0) {
            if ($oneCart != null) {
                $oneCart = ListCart::where('id', $oneCart->id)->first();
                $oneCart->update([
                    "discount" => 0
                ]);
            }
        }


        return [
            'code' => $code ?? 200,
            'success' => $success ?? true,
            'msg_code' => $msg_code ?? MsgCode::SUCCESS[0],
            'msg' => $msg ?? MsgCode::SUCCESS[1],
            'data' =>  [
                'total_before_discount' => $total_before_discount,
                'balance_collaborator_can_use' => $balance_collaborator_can_use,
                'balance_collaborator_used' => $balance_collaborator_used + $balance_collaborator_used_before,
                'balance_agency_can_use' => $balance_agency_can_use,
                'balance_agency_used' => $balance_agency_used + $balance_agency_used_before,
                'bonus_points_amount_can_use' => $bonus_points_amount_can_use,
                'total_points_can_use' => $total_points_can_use,
                'total_points_used' => $total_points_used,
                'bonus_points_amount_used' => $bonus_points_amount_used,
                'is_use_points' => $is_use_points,
                'is_order_for_customer' => $is_order_for_customer,
                'points_amount_used_edit_order' => $points_amount_used_edit_order,
                'points_total_used_edit_order' => $points_total_used_edit_order,
                'discount' => $discount,
                'ship_discount_amount' => $ship_discount_amount ?? 0,
                'total_shipping_fee' => $total_shipping_fee ?? 0,
                'product_discount_amount' => $product_discount_amount + $total_price_discount_has_edit,
                'total_after_discount' => $total_after_discount,
                'total_final' => $total_final,
                'total_final_before_override' => $total_final_before_override,
                'total_commission_order_for_customer' => $is_order_for_customer ? $total_commission_order_for_customer : 0,
                'used_discount' => $used_discount,
                'combo_discount_amount' => $combo_discount_amount,
                'share_collaborator' => $share_collaborator,
                'share_agency' => $share_agency,
                'point_for_agency' => $point_for_agency,
                'used_combos' => $used_combos,
                'voucher_discount_amount' =>  $voucher_discount_amount,
                'used_voucher' => $used_voucher,
                'line_items' => $allCart,
                'line_items_in_time' => $line_items_in_time,
                'bonus_agency' =>  $bonusAgency,
                'vat' => $vat,
                'package_weight' => $package_weight,
            ]
        ];
    }

    static public function response_info_cartV1($allCart, $request)
    {

        $data =  CustomerCartController::data_responseV1($allCart, $request);
        return response()->json(
            $data,
            $data['code']
        );
    }

    static function check_distributes_same($distributes_selected, $array_compare)
    {

        //Dạng $array_compare
        //   [
        //     "name" => "Chọn Vị"
        //     "value" => "Tự nhiên2"
        //     "sub_element_distributes" => "XL"
        //   ]

        $distributes_selected = $distributes_selected != null  &&  count($distributes_selected) > 0 ? (array)$distributes_selected[0] : null;
        $array_compare = $array_compare != null && count($array_compare) > 0 ? (array)$array_compare[0] : null;

        return $distributes_selected  == $array_compare;
    }

    static function get_distribute_array(Request $request)
    {

        //distributes
        $distributes = (array)$request->distributes;

        $distributes_add = null;

        if ($distributes != null && is_array($distributes) && count($distributes) > 0) {
            $distributes_add = [];

            foreach ($distributes  as $distribute) {
                if (isset($distribute['name']) && isset($distribute['value']))
                    array_push($distributes_add, [
                        'name' => $distribute['name'],
                        'value' => $distribute['value'],
                        'sub_element_distributes' => $distribute['sub_element_distributes'] ?? null,
                    ]);
                break;
            }
        }



        return  $distributes_add;
    }

    static function all_items_cart(Request $request, $list_cart_id = null, $is_bonus = null)
    {

        $allCart = null;

        $list_cart_id = $list_cart_id == null ? null : $list_cart_id;

        $allCart = CcartItem::allItem($list_cart_id,  $request)
            ->when($is_bonus  != null, function ($query) use ($is_bonus) {
                $query->where('is_bonus',  $is_bonus);
            })
            ->orderBy('created_at', 'asc')

            ->get();


        return   $allCart;
    }

    static function handle_bonus_product($request, $list_cart_id)
    {


        $used_bonus_products = [];

        function check_has_item_in_select($select_products, $cart_item_select)
        {

            foreach ($select_products as $select_product_bonus) {

                if (
                    $select_product_bonus->product_id == $cart_item_select->product_id &&
                    (($select_product_bonus->element_distribute_id == $cart_item_select->element_distribute_id &&
                        $select_product_bonus->sub_element_distribute_id == $cart_item_select->sub_element_distribute_id) ||
                        $select_product_bonus->allows_all_distribute
                    )
                ) {

                    return (int)($cart_item_select->quantity / $select_product_bonus->quantity);
                }
            }
            return -1;
        }

        function delete_is_bonus($request, $device_id,  $list_cart_id, $bonus_product_ids, $bonus_product_item_ids,  $bonus_product_item_ladder_ids)
        {

            $list_cart_id = $list_cart_id == null ? null : $list_cart_id;
            $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
            $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
            $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;
            $arrNeedRemove = [];

            $allCart = CcartItem::allItem($list_cart_id,  $request)
                ->where('is_bonus', true)
                ->get();
            $arrIdNeedRemove = array();

            foreach ($allCart as   $item) {
                if ($item->bonus_product_item_id != null && !in_array($item->bonus_product_item_id, $bonus_product_item_ids)) {
                    array_push($arrNeedRemove, $item->id);
                }
                if ($item->bonus_product_item_ladder_id != null && !in_array($item->bonus_product_item_ladder_id, $bonus_product_item_ladder_ids)) {
                    array_push($arrNeedRemove, $item->id);

                    foreach ($allCart as   $itemLadder) {

                        if ($item->id != $itemLadder->id) {

                            if (
                                $itemLadder->parent_cart_item_ids != "0" && $itemLadder->parent_cart_item_ids != null &&
                                $item->parent_cart_item_ids == $itemLadder->parent_cart_item_ids
                            ) {
                                array_push($arrNeedRemove, $itemLadder->id);
                            }
                        }
                    }
                }
            }

            CcartItem::where('is_bonus', true)->whereIn('id',   $arrIdNeedRemove)->delete();

            $allCart = CcartItem::allItem($list_cart_id,  $request)
                ->where('is_bonus', true)
                ->where('bonus_product_item_id', '!=', null)
                ->whereNotIn('bonus_product_item_id', $bonus_product_item_ids)
                ->delete();

            $allCart = CcartItem::allItem($list_cart_id,  $request)
                ->where('is_bonus', true)
                ->where('bonus_product_item_ladder_id', '!=', null)
                ->whereNotIn('bonus_product_item_ladder_id', $bonus_product_item_ladder_ids)
                ->delete();
        }

        $now = Helper::getTimeNowString();
        $BonusProducts = [];
        $BonusProductsMain = BonusProduct::where('store_id', $request->store->id)
            ->where('is_end', false)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->whereRaw('((bonus_products.amount - bonus_products.used > 0) OR bonus_products.set_limit_amount = false)')
            ->get();




        $device_id = request()->header('device_id');

        $bonus_product_ids = [];
        $bonus_product_item_ids = [];
        $bonus_product_item_ladder_ids = [];


        $customer = $request->customer;


        foreach ($BonusProductsMain  as   $BonusProductsM) {

            $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                $request,
                $BonusProductsM->group_customer,
                $BonusProductsM->agency_type_id,
                $BonusProductsM->group_type_id,
                $customer,
                $request->store->id,
                $BonusProductsM->group_customers,
                $BonusProductsM->agency_types,
                $BonusProductsM->group_types
            );



            if ($ok_customer) {
                array_push($BonusProducts, $BonusProductsM);
            }
        }



        if (count($BonusProducts) == 0) {

            //Xóa tất cả phần thưởng
            delete_is_bonus($request, $device_id, $list_cart_id, $bonus_product_ids, $bonus_product_item_ids, $bonus_product_item_ladder_ids);
        } else
        if (count($BonusProducts) > 0) {

            //Lấy tất cả item để check thưởng
            $items = CustomerCartController::all_items_cart($request, $list_cart_id, false);


            $list_cart_id = $list_cart_id == null ? null : $list_cart_id;
            $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
            $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
            $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;

            $device_id =  $device_id;





            foreach ($BonusProducts as $bonusProduct) {

                //đây là tặng thưởng theo cấp số cộng
                if ($bonusProduct->ladder_reward == false) {


                    $group_product_ids =  BonusProductItem::where('bonus_product_id', $bonusProduct->id)->where('is_select_product', true)
                        ->distinct()->pluck('group_product')
                        ->toArray();

                    foreach ($group_product_ids as $group_product_id) {
                        $select_products = BonusProductItem::where('bonus_product_id', $bonusProduct->id)->where('group_product', $group_product_id)->where('is_select_product', true)->get();
                        $mul = null;
                        $total_valid = 0;


                        $length_cart_item_same = 0;
                        foreach ($select_products   as  $select_product_bonus) {
                            $has = false;
                            foreach ($items  as   $item_cart) {
                                if (
                                    $item_cart->is_bonus == false &&
                                    $select_product_bonus->product_id ==  $item_cart->product_id
                                    &&
                                    (($select_product_bonus->element_distribute_id ==  $item_cart->element_distribute_id
                                        &&
                                        $select_product_bonus->sub_element_distribute_id ==  $item_cart->sub_element_distribute_id) ||
                                        $select_product_bonus->allows_all_distribute == true
                                    )
                                ) {

                                    $has = true;
                                    break;
                                }
                            }
                            if ($has == true) {
                                $length_cart_item_same++;
                            }
                        }



                        $parent_cart_item_ids = "";
                        if ($length_cart_item_same >= count($select_products)) {


                            foreach ($items as $cart_item_select) {

                                //Kiểm tra 1 item có nằm trong selected của chương trình thưởng ($mul sẽ lưu số lượng sp đc thưởng)
                                $quantity = check_has_item_in_select($select_products, $cart_item_select);


                                if ($cart_item_select->is_bonus == false && $quantity != -1 && ($mul === null || $quantity <=  $mul)) {
                                    $mul  =  $quantity;
                                }

                                if ($cart_item_select->is_bonus == false && $quantity > 0) {
                                    $total_valid++;
                                    $parent_cart_item_ids =  $parent_cart_item_ids . $cart_item_select->id . ";";
                                }
                            }
                        }

                        //Đúng điều kiện
                        if ($mul > 0 && count($select_products) == $total_valid) {


                            $bonus_products = BonusProductItem::where('bonus_product_id',  $bonusProduct->id)->where('group_product', $group_product_id)->where('is_select_product', false)->get(); //Lấy danh sách sp thưởng



                            foreach ($bonus_products as $bonus_product) {
                                $distributes_add = array();
                                array_push($distributes_add, [
                                    'name' => $bonus_product->distribute_name,
                                    'value' => $bonus_product->element_distribute_name,
                                    'sub_element_distributes' => $bonus_product->sub_element_distribute_name,
                                ]);


                                $cartExists = CcartItem::allItem($list_cart_id,  $request)
                                    ->where('is_bonus', true)
                                    ->where('bonus_product_item_id', $bonus_product->id)  //id cua item phan thuongg 
                                    ->first();



                                if ($cartExists  != null) {
                                    $cartExists->update([
                                        'list_cart_id' => $list_cart_id,
                                        'store_id' => $request->store->id,
                                        'customer_id' =>  $customer_id,
                                        'user_id' => $user_id,
                                        'device_id' =>  $device_id,
                                        'staff_id' => $staff_id,

                                        'product_id' => $bonus_product->product_id,
                                        'quantity' =>  $bonusProduct->multiply_by_number == true ? ($mul * $bonus_product->quantity) : $bonus_product->quantity,
                                        // 'distributes' =>  json_encode($distributes_add),
                                        // 'element_distribute_id' => $bonus_product->element_distribute_id,
                                        // 'sub_element_distribute_id' => $bonus_product->sub_element_distribute_id,
                                        'is_bonus' => true,
                                        'bonus_product_name' => $bonusProduct->name,
                                        'parent_cart_item_ids' => $parent_cart_item_ids,
                                        "item_price" => 0,
                                        "bonus_product_id" => $bonusProduct->id,
                                        "bonus_product_item_id" => $bonus_product->id,
                                        'allows_choose_distribute' => $bonus_product->allows_choose_distribute
                                    ]);
                                } else {

                                    $distribute_name = $bonus_product->distribute_name;
                                    $element_distribute_name = $bonus_product->element_distribute_name;
                                    $sub_element_distribute_name = $bonus_product->sub_element_distribute_name;
                                    $element_distribute_id = $bonus_product->element_distribute_id;
                                    $sub_element_distribute_id = $bonus_product->sub_element_distribute_id;

                                    if ($bonus_product->allows_choose_distribute == true) {
                                        $data_choose = ProductUtils::auto_choose_distribute(Product::where('id', $bonus_product->product_id)->first());

                                        $distribute_name = $data_choose['distribute_name'] ?? null;
                                        $element_distribute_name = $data_choose['element_distribute_name'] ?? null;
                                        $sub_element_distribute_name = $data_choose['sub_element_distribute_name'] ?? null;

                                        $element_distribute_id = $data_choose['element_distribute_id'] ?? null;
                                        $sub_element_distribute_id = $data_choose['sub_element_distribute_id'] ?? null;
                                    }


                                    $distributes_add = array();
                                    array_push($distributes_add, [
                                        'name' =>  $distribute_name,
                                        'value' => $element_distribute_name,
                                        'sub_element_distributes' =>  $sub_element_distribute_name,
                                    ]);



                                    $cartExists = CcartItem::create(
                                        [
                                            'list_cart_id' => $list_cart_id,
                                            'store_id' => $request->store->id,
                                            'customer_id' =>  $customer_id,
                                            'user_id' => $user_id,
                                            'staff_id' => $staff_id,
                                            'device_id' =>  $device_id,

                                            'product_id' => $bonus_product->product_id,
                                            'quantity' =>  $bonusProduct->multiply_by_number == true ? ($mul * $bonus_product->quantity) : $bonus_product->quantity,
                                            'distributes' =>  json_encode($distributes_add),
                                            'element_distribute_id' =>   $element_distribute_id,
                                            'sub_element_distribute_id' =>  $sub_element_distribute_id,
                                            'is_bonus' => true,
                                            'parent_cart_item_ids' => $parent_cart_item_ids,
                                            'bonus_product_name' => $bonusProduct->name,
                                            "item_price" => 0,
                                            "bonus_product_id" => $bonusProduct->id,
                                            "bonus_product_item_id" => $bonus_product->id,
                                            'allows_choose_distribute' => $bonus_product->allows_choose_distribute
                                        ]
                                    );
                                }

                                array_push($bonus_product_item_ids, $bonus_product->id);
                                array_push(
                                    $used_bonus_products,
                                    [
                                        'quantity' =>  $bonusProduct->multiply_by_number == true ? $mul :  $bonus_product->quantity,
                                        'bonus_product' => [
                                            "id" => $bonusProduct->id,
                                            "name" => $bonusProduct->name,
                                            "start_time" => $bonusProduct->start_time,
                                            "end_time" => $bonusProduct->end_time,
                                            "multiply_by_number" => $bonusProduct->multiply_by_number,
                                        ]
                                    ],
                                );
                            }
                        } else { //Xóa nó nếu đã bị bớt và không đủ điều kiện sau khi chỉnh 1 số sp 


                            CcartItem::allItem($list_cart_id,  $request)
                                ->where('is_bonus', true)
                                ->where('group_product_id',  $group_product_id)
                                ->where('bonus_product_id', $bonusProduct->id)  //id cua item phan thuongg 
                                ->delete();
                        }
                    }
                } else { //đây là tặng thưởng theo cấp bậc thang


                    foreach ($items  as   $item_cart) {
                        if ($item_cart->is_bonus == true) continue;

                        $bonus_pro = BonusProductItemLadder::where('bonus_product_id', $bonusProduct->id)
                            ->where('element_distribute_id', $item_cart->element_distribute_id)
                            ->where('sub_element_distribute_id', $item_cart->sub_element_distribute_id)
                            ->where('product_id', $item_cart->product_id)
                            ->where('from_quantity', '<=',  $item_cart->quantity)
                            ->orderBy('from_quantity', 'desc')->first();

                        $parent_cart_item_ids = "";
                        if ($bonus_pro != null) {

                            $parent_cart_item_ids = $item_cart->id . ";";

                            $distributes_add = array();
                            array_push($distributes_add, [
                                'name' => $bonus_pro->bo_distribute_name,
                                'value' => $bonus_pro->bo_element_distribute_name,
                                'sub_element_distributes' => $bonus_pro->bo_sub_element_distribute_name,
                            ]);


                            $cartExists = CcartItem::allItem($list_cart_id,  $request)
                                ->where('is_bonus', true)
                                ->where('bonus_product_item_ladder_id', $bonus_pro->id)  //id cua item phan thuongg 
                                ->first();


                            if ($cartExists  == null) {

                                $cartExists = CcartItem::create(
                                    [
                                        'list_cart_id' => $list_cart_id,
                                        'store_id' => $request->store->id,
                                        'customer_id' =>  $customer_id,
                                        'user_id' => $user_id,
                                        'staff_id' => $staff_id,
                                        'device_id' =>  $device_id,

                                        'product_id' => $bonus_pro->bo_product_id,
                                        'quantity' => $bonus_pro->bo_quantity,
                                        'distributes' =>  json_encode($distributes_add),
                                        'element_distribute_id' =>   $bonus_pro->bo_element_distribute_id,
                                        'sub_element_distribute_id' =>  $bonus_pro->bo_sub_element_distribute_id,
                                        'is_bonus' => true,
                                        'parent_cart_item_ids' =>  $parent_cart_item_ids,
                                        'bonus_product_name' => $bonusProduct->name,
                                        "item_price" => 0,
                                        "bonus_product_id" => $bonusProduct->id,
                                        "bonus_product_item_ladder_id" => $bonus_pro->id,
                                        //'allows_choose_distribute' => $bonus_product->allows_choose_distribute
                                    ]
                                );
                            }


                            array_push(
                                $used_bonus_products,
                                [
                                    'quantity' =>  $bonus_pro->bo_quantity,
                                    'bonus_product' => [
                                        "id" => $bonusProduct->id,
                                        "name" => $bonusProduct->name,
                                        "start_time" => $bonusProduct->start_time,
                                        "end_time" => $bonusProduct->end_time,
                                        "multiply_by_number" => $bonusProduct->multiply_by_number,
                                    ]
                                ],
                            );


                            array_push($bonus_product_item_ladder_ids, $bonus_pro->id);
                        }
                    }
                }
            }


            //Xóa tất cả phần thưởng
            delete_is_bonus($request, $device_id, $list_cart_id, $bonus_product_ids, $bonus_product_item_ids, $bonus_product_item_ladder_ids);
        }


        return $used_bonus_products;
    }



    /**
     * Thêm sản phẩm vào giỏ hàng
     * 
     * header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)
     * 
     * @urlParam  store_code required Store code
     * @bodyParam product_id int required Product id
     * @bodyParam distributes List danh sách phân loại đã chọn vd: [  {name:"màu", value:"đỏ", sub_element_distributes:"XL"} ]
     * @bodyParam "code_voucher":"SUPER" gửi code voucher
     * @bodyParam is_use_points có sử dụng điểm thưởng hay không
     * @bodyParam  is_use_balance_collaborator su dung diem CTV
     */
    public function addLineItem(Request $request, $id)
    {

        $device_id = request()->header('device_id');

        $distribute_array = $this->get_distribute_array($request);
        $distributes_add  = json_encode($distribute_array);
        /////



        $product_id = $request->product_id;

        $productExists = Product::where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            $product_id
        )->first();

        if (empty($productExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }


        if (!($productExists->distributes != null && count($productExists->distributes) > 0)) {
            $distribute_array = array();
        }


        $itemExists = null;
        $items = CustomerCartController::all_items_cart($request, null, false);

        if (count($items) >= 40) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thứ lỗi giỏ hàng chỉ chứa tối đa 40 sản phẩm",
            ], 400);
        }


        foreach ($items as $item) {
            if ($item->is_bonus == false && $request->product_id == $item->product_id && $this->check_distributes_same($item->distributes_selected, $distribute_array) == true) {
                $itemExists = $item;
                break;
            };
        }



        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];

        $distribute_id = null;
        $element_distribute_id = null;
        $sub_element_distribute_id = null;

        //Kiểm tra có cả 3
        if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();


            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute->id;

                    $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('name', $distribute_array[0]['sub_element_distributes'])->where('store_id', $request->store->id)->first();


                    $sub_element_distribute_id = $sub_ele_distribute == null ? null :  $sub_ele_distribute->id;
                }
            }
        } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {


            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();
            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute == null ? null : $ele_distribute->id;
                }
            }
        }

        //////////////////////////////////////////////////////////////////////////////////////////////

        if ($productExists->check_inventory == false ||   $allow_semi_negative == true) {
            $next_quantity = ($itemExists == null ? 0 : $itemExists->quantity) +   $request->quantity;
        } else {
            ///Xử lý số lượng tồn kho
            $max_quantity = $productExists->quantity_in_stock;

            //Kiểm tra có cả 3
            if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->join('sub_element_distributes', 'sub_element_distributes.element_distribute_id', '=', 'element_distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->where('sub_element_distributes.name', '=',  $distribute_array[0]['sub_element_distributes'])
                    ->first();


                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {

                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->first();
                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            }


            $next_quantity = $request->quantity;
            if (empty($itemExists)) {
                $next_quantity = $request->quantity;
            } else {
                $next_quantity = $itemExists->quantity + $request->quantity;
            }

            if ($max_quantity  >= 0) {
                if ($next_quantity  > $max_quantity) {
                    $next_quantity  = $max_quantity;
                }
            }
        }


        $list_cart_id = null;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;



        if (empty($itemExists)) {

            $lineItem = CcartItem::create(
                [
                    'store_id' => $request->store->id,

                    'list_cart_id' =>  $list_cart_id,
                    'customer_id' =>  $customer_id,
                    'device_id' =>   $device_id,
                    'user_id' =>   $user_id,
                    'staff_id' =>   $staff_id,

                    'product_id' => $product_id,
                    'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                    'distributes' => $distributes_add,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'allows_choose_distribute' => true
                ]
            );
        } else {

            $itemExists->update(
                [
                    'list_cart_id' =>  $list_cart_id,
                    'customer_id' =>  $customer_id,
                    'device_id' =>   $device_id,
                    'user_id' =>   $user_id,
                    'staff_id' =>   $staff_id,

                    'quantity' => $next_quantity ?? 1,
                    'distributes' => $distributes_add == null ? $itemExists->distributes :  $distributes_add,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'allows_choose_distribute' => true
                ]
            );
        }




        return $this->getAll($request);
    }

    public function addLineItemV1(Request $request, $id)
    {
        $device_id = request()->header('device_id');

        $distribute_array = $this->get_distribute_array($request);
        $distributes_add  = json_encode($distribute_array);
        /////



        $product_id = $request->product_id;

        $productExists = Product::where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            $product_id
        )->first();

        if (empty($productExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }


        if (!($productExists->distributes != null && count($productExists->distributes) > 0)) {
            $distribute_array = array();
        }


        $itemExists = null;
        $items = CustomerCartController::all_items_cart($request, null, false);

        if (count($items) >= 40) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thứ lỗi giỏ hàng chỉ chứa tối đa 40 sản phẩm",
            ], 400);
        }


        foreach ($items as $item) {
            if ($item->is_bonus == false && $request->product_id == $item->product_id && $this->check_distributes_same($item->distributes_selected, $distribute_array) == true) {
                $itemExists = $item;
                break;
            };
        }



        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];

        $distribute_id = null;
        $element_distribute_id = null;
        $sub_element_distribute_id = null;

        //Kiểm tra có cả 3
        if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();


            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute->id;

                    $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('name', $distribute_array[0]['sub_element_distributes'])->where('store_id', $request->store->id)->first();


                    $sub_element_distribute_id = $sub_ele_distribute == null ? null :  $sub_ele_distribute->id;
                }
            }
        } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {


            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();
            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute == null ? null : $ele_distribute->id;
                }
            }
        }

        //////////////////////////////////////////////////////////////////////////////////////////////

        if ($productExists->check_inventory == false ||   $allow_semi_negative == true) {
            $next_quantity = ($itemExists == null ? 0 : $itemExists->quantity) +   $request->quantity;
        } else {
            ///Xử lý số lượng tồn kho
            $max_quantity = $productExists->quantity_in_stock;

            //Kiểm tra có cả 3
            if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->join('sub_element_distributes', 'sub_element_distributes.element_distribute_id', '=', 'element_distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->where('sub_element_distributes.name', '=',  $distribute_array[0]['sub_element_distributes'])
                    ->first();


                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {

                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->first();
                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            }


            $next_quantity = $request->quantity;
            if (empty($itemExists)) {
                $next_quantity = $request->quantity;
            } else {
                $next_quantity = $itemExists->quantity + $request->quantity;
            }

            if ($max_quantity  >= 0) {
                if ($next_quantity  > $max_quantity) {
                    $next_quantity  = $max_quantity;
                }
            }
        }


        $list_cart_id = null;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;



        if (empty($itemExists)) {

            $lineItem = CcartItem::create(
                [
                    'store_id' => $request->store->id,

                    'list_cart_id' =>  $list_cart_id,
                    'customer_id' =>  $customer_id,
                    'device_id' =>   $device_id,
                    'user_id' =>   $user_id,
                    'staff_id' =>   $staff_id,

                    'product_id' => $product_id,
                    'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                    'distributes' => $distributes_add,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'allows_choose_distribute' => true
                ]
            );
        } else {

            $itemExists->update(
                [
                    'list_cart_id' =>  $list_cart_id,
                    'customer_id' =>  $customer_id,
                    'device_id' =>   $device_id,
                    'user_id' =>   $user_id,
                    'staff_id' =>   $staff_id,

                    'quantity' => $next_quantity ?? 1,
                    'distributes' => $distributes_add == null ? $itemExists->distributes :  $distributes_add,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'allows_choose_distribute' => true
                ]
            );
        }




        return $this->getAllV1($request);
    }
    /**
     * Cập nhật 1 sản phẩm trong giỏ hàng
     * 
     * header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)
     * 
     * @urlParam  store_code required Store code
     * @bodyParam quantity int required Số lượng (Nếu == 0 xóa luôn sản phẩm khỏi giỏ)
     * @bodyParam product_id int required Product id (bat buoc phai co)
     * @bodyParam line_item_id int required Trường hợp cần cập nhật phân loại mới gửi sp mới thì để null
     * @bodyParam distributes List danh sách phân loại đã chọn vd: [  {name:"màu", value:"đỏ"} ]
     * @bodyParam code_vouche string ":"SUPER" gửi code voucher
     * @bodyParam is_use_points boolean có sử dụng điểm thưởng hay không
     * @bodyParam  is_use_balance_collaborator boolean su dung diem CTV
     */
    public function updateLineItem(Request $request, $id)
    {

        //distributes
        $distribute_array = $this->get_distribute_array($request);
        $distributes_add  = json_encode($distribute_array);
        ///

        $product_id = $request->product_id;
        $quantity = $request->quantity;
        $line_item_id = $request->line_item_id;

        if ($quantity === null || ($quantity < 0)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_QUANTITY[0],
                'msg' => MsgCode::INVALID_QUANTITY[1],
            ], 400);
        }

        $productExists = Product::where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            $product_id
        )->first();


        if (empty($productExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }


        /////////////////////// xử lý distribute phân loại
        $distribute_id = null;
        $element_distribute_id = null;
        $sub_element_distribute_id = null;


        //Kiểm tra có cả 3
        if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();


            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute->id;

                    $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('name', $distribute_array[0]['sub_element_distributes'])->where('store_id', $request->store->id)->first();


                    $sub_element_distribute_id = $sub_ele_distribute == null ? null :  $sub_ele_distribute->id;
                }
            }
        } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {


            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();
            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute == null ? null : $ele_distribute->id;
                }
            }
        }


        //distributes
        $list_cart_id = null;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;

        $device_id = request()->header('device_id');
        $itemExists  = null;

        if ($line_item_id === null) {

            $items = CustomerCartController::all_items_cart($request, null, false);
            foreach ($items as $item) {
                if ($request->product_id == $item->product_id && $this->check_distributes_same($item->distributes_selected, $distribute_array) == true) {
                    $itemExists = $item;
                    break;
                };
            }
        } else { //Có line item

            $LineItemExists = CcartItem::where(
                'store_id',
                $request->store->id
            )->where(
                'id',
                $line_item_id
            )->first();

            if ($LineItemExists  == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm đã bị xóa khỏi giỏ",
                ], 400);
            }

            if ($LineItemExists != null &&   $LineItemExists->is_bonus == true && $request->quantity != $LineItemExists->quantity) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng không thể thay đổi số lượng",
                ], 400);
            }

            if ($LineItemExists != null &&   $LineItemExists->is_bonus == true &&   $LineItemExists->allows_choose_distribute == false && ($element_distribute_id != $LineItemExists->element_distribute_id ||
                $sub_element_distribute_id != $LineItemExists->sub_element_distribute_id
            )) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng này không thể thay đổi phân loại",
                ], 400);
            }

            $quantity_same = null;
            if ($LineItemExists->is_bonus == false) {


                $itemSame = null;

                //Lấy danh sách cart item có product giống nhưng khác line item hiện tại
                $itemsCompare = CcartItem::allItem($list_cart_id,  $request)
                    ->where('is_bonus', false)
                    ->where(
                        'product_id',
                        $product_id
                    )->where(
                        'id',
                        '<>',
                        $line_item_id
                    )
                    ->get();

                //Tìm product giống
                foreach ($itemsCompare as $item) {
                    if (
                        $line_item_id != $item->id &&
                        $LineItemExists->product_id ==
                        $item->product_id &&
                        $this->check_distributes_same($item->distributes_selected, $distribute_array) == true
                    ) {
                        $itemSame = $item;
                        break;
                    };
                }

                $quantity_same = $itemSame == null ? 0 : $itemSame->quantity;


                //giống thì xóa
                if ($itemSame != null)  $itemSame->delete();
            }

            //Thêm hoặc cập nhật
            $itemExists = CcartItem::where(
                'store_id',
                $request->store->id
            )->when($request->customer != null, function ($query) use ($device_id, $request) {

                $query->where(function ($query) use ($device_id, $request) {
                    $query->where(
                        'customer_id',
                        $request->customer->id
                    )->when($device_id != null, function ($query) use ($device_id, $request) {
                        $query->orWhere('device_id', $device_id);
                    });
                });
            })
                ->when($request->customer == null && $request->device_id != null, function ($query) use ($device_id, $request) {
                    $query->where('device_id', $device_id);
                })->where(
                    'id',
                    $line_item_id
                )->first();
        }


        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];



        //////////////////////////////////////////////////////////////////////////////////////////////

        if ($productExists->check_inventory == false ||   $allow_semi_negative == true) {

            $next_quantity = $request->quantity;
        } else {

            //Tính toán xử lý kho còn lại
            $max_quantity = $productExists->quantity_in_stock;

            //Kiểm tra có cả 3
            if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->join('sub_element_distributes', 'sub_element_distributes.element_distribute_id', '=', 'element_distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->where('sub_element_distributes.name', '=',  $distribute_array[0]['sub_element_distributes'])
                    ->first();


                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {
                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->first();
                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            }


            $next_quantity = $quantity + $quantity_same;


            if ($productExists->check_inventory == true && $max_quantity  >= 0) {
                if ($next_quantity  > $max_quantity) {
                    $next_quantity  = $max_quantity;
                }
            }
        }

        if (empty($itemExists)) {
            if ($quantity > 0) {
                $lineItem = CcartItem::create(
                    [

                        'store_id' => $request->store->id,
                        'customer_id' =>  $customer_id,
                        'product_id' => $product_id,
                        'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                        'note' =>  $request->note,
                        'distributes' => $distributes_add,
                        'device_id' =>  $device_id,
                        'user_id' => $user_id,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id
                    ]
                );
            }
        } else {

            if ($quantity == 0) {
                $itemExists->delete();
            } else {

                $itemExists->update(
                    Helper::sahaRemoveItemArrayIfNullValue([
                        'user_id' => $user_id,
                        'customer_id' =>  $customer_id,
                        'device_id' =>  $device_id,
                        'note' =>  $request->note,
                        'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                        'distributes' => $distributes_add == null ? $itemExists->distributes :  $distributes_add,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id
                    ])
                );
            }
        }

        return $this->getAll($request);
    }

    public function updateLineItemV1(Request $request, $id)
    {

        //distributes
        $distribute_array = $this->get_distribute_array($request);
        $distributes_add  = json_encode($distribute_array);
        ///

        $product_id = $request->product_id;
        $quantity = $request->quantity;
        $line_item_id = $request->line_item_id;

        if ($quantity === null || ($quantity < 0)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_QUANTITY[0],
                'msg' => MsgCode::INVALID_QUANTITY[1],
            ], 400);
        }

        $productExists = Product::where(
            'store_id',
            $request->store->id
        )->where(
            'id',
            $product_id
        )->first();


        if (empty($productExists)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }


        /////////////////////// xử lý distribute phân loại
        $distribute_id = null;
        $element_distribute_id = null;
        $sub_element_distribute_id = null;


        //Kiểm tra có cả 3
        if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();


            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute->id;

                    $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('element_distribute_id', $ele_distribute->id)
                        ->where('name', $distribute_array[0]['sub_element_distributes'])->where('store_id', $request->store->id)->first();


                    $sub_element_distribute_id = $sub_ele_distribute == null ? null :  $sub_ele_distribute->id;
                }
            }
        } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {


            $distribute =    Distribute::where('product_id', $product_id)
                ->where('name', $distribute_array[0]['name'])->where('store_id', $request->store->id)->first();
            if ($distribute != null) {
                $distribute_id =  $distribute->id;

                $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                    ->where('distribute_id', $distribute->id)
                    ->where('name', $distribute_array[0]['value'])->where('store_id',  $request->store->id)->first();
                if ($ele_distribute != null) {

                    $element_distribute_id =   $ele_distribute == null ? null : $ele_distribute->id;
                }
            }
        }


        //distributes
        $list_cart_id = null;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;

        $device_id = request()->header('device_id');
        $itemExists  = null;

        if ($line_item_id === null) {

            $items = CustomerCartController::all_items_cart($request, null, false);
            foreach ($items as $item) {
                if ($request->product_id == $item->product_id && $this->check_distributes_same($item->distributes_selected, $distribute_array) == true) {
                    $itemExists = $item;
                    break;
                };
            }
        } else { //Có line item

            $LineItemExists = CcartItem::where(
                'store_id',
                $request->store->id
            )->where(
                'id',
                $line_item_id
            )->first();

            if ($LineItemExists  == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm đã bị xóa khỏi giỏ",
                ], 400);
            }

            if ($LineItemExists != null &&   $LineItemExists->is_bonus == true && $request->quantity != $LineItemExists->quantity) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng không thể thay đổi số lượng",
                ], 400);
            }

            if ($LineItemExists != null &&   $LineItemExists->is_bonus == true &&   $LineItemExists->allows_choose_distribute == false && ($element_distribute_id != $LineItemExists->element_distribute_id ||
                $sub_element_distribute_id != $LineItemExists->sub_element_distribute_id
            )) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng này không thể thay đổi phân loại",
                ], 400);
            }

            $quantity_same = null;
            if ($LineItemExists->is_bonus == false) {


                $itemSame = null;

                //Lấy danh sách cart item có product giống nhưng khác line item hiện tại
                $itemsCompare = CcartItem::allItem($list_cart_id,  $request)
                    ->where('is_bonus', false)
                    ->where(
                        'product_id',
                        $product_id
                    )->where(
                        'id',
                        '<>',
                        $line_item_id
                    )
                    ->get();

                //Tìm product giống
                foreach ($itemsCompare as $item) {
                    if (
                        $line_item_id != $item->id &&
                        $LineItemExists->product_id ==
                        $item->product_id &&
                        $this->check_distributes_same($item->distributes_selected, $distribute_array) == true
                    ) {
                        $itemSame = $item;
                        break;
                    };
                }

                $quantity_same = $itemSame == null ? 0 : $itemSame->quantity;


                //giống thì xóa
                if ($itemSame != null)  $itemSame->delete();
            }

            //Thêm hoặc cập nhật
            $itemExists = CcartItem::where(
                'store_id',
                $request->store->id
            )->when($request->customer != null, function ($query) use ($device_id, $request) {

                $query->where(function ($query) use ($device_id, $request) {
                    $query->where(
                        'customer_id',
                        $request->customer->id
                    )->when($device_id != null, function ($query) use ($device_id, $request) {
                        $query->orWhere('device_id', $device_id);
                    });
                });
            })
                ->when($request->customer == null && $request->device_id != null, function ($query) use ($device_id, $request) {
                    $query->where('device_id', $device_id);
                })->where(
                    'id',
                    $line_item_id
                )->first();
        }


        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];



        //////////////////////////////////////////////////////////////////////////////////////////////

        if ($productExists->check_inventory == false ||   $allow_semi_negative == true) {

            $next_quantity = $request->quantity;
        } else {

            //Tính toán xử lý kho còn lại
            $max_quantity = $productExists->quantity_in_stock;

            //Kiểm tra có cả 3
            if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value']) && isset($distribute_array[0]['sub_element_distributes'])) {

                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->join('sub_element_distributes', 'sub_element_distributes.element_distribute_id', '=', 'element_distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->where('sub_element_distributes.name', '=',  $distribute_array[0]['sub_element_distributes'])
                    ->first();


                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            } else  if (isset($distribute_array[0]['name']) && isset($distribute_array[0]['value'])) {
                $sub_element = DB::table('products')
                    ->where('products.id', '=',  $product_id)
                    ->join('distributes', 'products.id', '=', 'distributes.product_id')
                    ->join('element_distributes', 'element_distributes.distribute_id', '=', 'distributes.id')
                    ->where('distributes.name', '=', $distribute_array[0]['name'])
                    ->where('element_distributes.name', '=',  $distribute_array[0]['value'])
                    ->first();
                if ($sub_element != null) {
                    $max_quantity = $sub_element->quantity_in_stock;
                }
            }


            $next_quantity = $quantity + $quantity_same;


            if ($productExists->check_inventory == true && $max_quantity  >= 0) {
                if ($next_quantity  > $max_quantity) {
                    $next_quantity  = $max_quantity;
                }
            }
        }

        if (empty($itemExists)) {
            if ($quantity > 0) {
                $lineItem = CcartItem::create(
                    [

                        'store_id' => $request->store->id,
                        'customer_id' =>  $customer_id,
                        'product_id' => $product_id,
                        'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                        'note' =>  $request->note,
                        'distributes' => $distributes_add,
                        'device_id' =>  $device_id,
                        'user_id' => $user_id,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id
                    ]
                );
            }
        } else {

            if ($quantity == 0) {
                $itemExists->delete();
            } else {

                $itemExists->update(
                    Helper::sahaRemoveItemArrayIfNullValue([
                        'user_id' => $user_id,
                        'customer_id' =>  $customer_id,
                        'device_id' =>  $device_id,
                        'note' =>  $request->note,
                        'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                        'distributes' => $distributes_add == null ? $itemExists->distributes :  $distributes_add,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id
                    ])
                );
            }
        }

        return $this->getAllV1($request);
    }

    /**
     * Danh sách sản phẩm trong giỏ hàng
     * @urlParam  store_code required Store code
     * 
     * giá của mỗi item item_price (tính toán dựa trên phân loại đã chọn)
     * 
     * header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)
     * 
     * @bodyParam "code_voucher":"SUPER" gửi code voucher
     * @bodyParam is_use_points có sử dụng điểm thưởng hay không
     * @bodyParam  is_use_balance_collaborator su dung diem CTV
     */
    public function updateCartInfo(Request $request)
    {
        $this->handle_bonus_product($request, null);
        $allCart = CustomerCartController::all_items_cart($request);
        return $this->response_info_cart($allCart, $request);
    }

    public function updateCartInfoV1(Request $request)
    {
        $this->handle_bonus_product($request, null);
        $allCart = CustomerCartController::all_items_cart($request);
        return $this->response_info_cartV1($allCart, $request);
    }

    /**
     * Danh sách sản phẩm trong giỏ hàng
     * @urlParam  store_code required Store code
     * 
     */
    public function getAll(Request $request)
    {

        $this->handle_bonus_product($request, null);

        $allCart = CustomerCartController::all_items_cart($request);

        return $this->response_info_cart($allCart, $request);
    }

    public function getAllV1(Request $request)
    {

        $this->handle_bonus_product($request, null);

        $allCart = CustomerCartController::all_items_cart($request);

        return $this->response_info_cartV1($allCart, $request);
    }

    /**
     * Chi tiết các thời gian giao cũng như phí vận chuyển từng loại giao hàng
     * 
     * @bodyParam id_address_customer integer required Id địa chỉ giao hàng
     * @bodyParam service_type integer required Kiểu giao (siêu tốc hoặc chậm)
     * @bodyParam province_id integer required Id tỉnh (ko login)
     * @bodyParam district_id integer required Id quận (ko login)
     * @bodyParam wards_id integer required Id huyện (ko login)
     * @bodyParam partner_id required id nhà vận chuyển
     * 
     */
    public function calculate_fee_by_partner_id(Request $request)
    {
        $branch_id = null;
        if ($request->branch_id) {
            $branch_id = $request->branch_id;
        } else if ($request->branch != null) {
            $branch_id = $request->branch->id;
        } else {
            $branch_id = BranchUtils::getBranchDefaultOrderOnline($request->store->id)->id;
        }

        $partner_id = $request->partner_id;

        $config = ConfigShipController::defaultDataConfigShip($request->store->id);

        if (!$request->user && !$request->staff) {

            $package_weight = 0;
            $total_final = 0;
            $allCart = CustomerCartController::all_items_cart($request);
            $data =  CustomerCartController::data_response($allCart, $request);


            foreach ($allCart as $lineItem) {

                //tính khối lượng
                $package_weight = $package_weight + (($lineItem->product->weight <= 0 ? 100 : $lineItem->product->weight) * $lineItem->quantity);
                $total_final = $total_final + (($lineItem->item_price <= 0 ? 100 : $lineItem->item_price) * $lineItem->quantity);
            }
            if ($package_weight <= 0) {
                $package_weight = 100;
            }
        }

        if ($partner_id  == -1) {
            if (!$request->user) {
                $addressCustomerExists = CustomerAddress::where(
                    'store_id',
                    $request->store->id
                )
                    ->where(
                        'customer_id',
                        $request->customer == null ? null : $request->customer->id
                    )
                    ->where('id',  $request->id_address_customer)->first();
            }

            if ($config->use_fee_from_default == true) {
                $is_checke_fee = $request->user ? in_array(
                    $request->receiver_province_id ? $request->receiver_province_id : null,
                    $config->urban_list_id_province
                ) : in_array(
                    $addressCustomerExists == null ? $request->province_id : $addressCustomerExists->province,
                    $config->urban_list_id_province
                );
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => [
                        "partner_id" =>  -1,
                        "name" => "Phí giao hàng mặc định",
                        'fee_with_type_ship' => [
                            [
                                "description" => $config->fee_default_description  ?? "",
                                "fee" =>  $is_checke_fee ? $config->fee_urban :   $config->fee_suburban,
                                "ship_speed_code" => 0,
                            ]
                        ],
                    ],
                ], 200);
            } else {
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => array(),
                ], 200);
            }
        } else {

            $addressPickupExists = StoreAddress::where(
                'store_id',
                $request->store->id
            )->when($branch_id != null, function ($query)  use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })->where('is_default_pickup', true)->first();

            if ($addressPickupExists == null) {
                $addressPickupExists = StoreAddress::where(
                    'store_id',
                    $request->store->id
                )->where('branch_id', null)->where('is_default_pickup', true)
                    ->first();
            }

            if (empty($addressPickupExists)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'data' => [],
                    'msg_code' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[0],
                    'msg' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[1],
                ], 200);
            }

            if ($request->user != null || $request->staff) {

                $shipperArr = array(
                    'money_collection' => $request->money_collection,
                    "store_id" => $request->store->id,
                    "from_province_id" =>  $request->sender_province_id ?? $addressPickupExists->province,
                    "from_district_id" => $request->sender_district_id ?? $addressPickupExists->district,
                    "from_wards_id" =>  $request->sender_wards_id ?? $addressPickupExists->wards,
                    "to_province_id" => $request->receiver_province_id,
                    "to_district_id" => $request->receiver_district_id,
                    "to_wards_id" => $request->receiver_wards_id,
                    "service_type" => $request->service_type,
                    "customer_name" => $request->customer_name,
                    "to_address_detail" => $request->receiver_address,
                    "weight" =>  $request->weight,
                    "length" => $request->length,
                    "width" => $request->width,
                    "height" => $request->height,
                );

                // $orderExists = Order::where('store_id', $request->store->id)
                //     ->where('order_code', $request->order_code)
                //     ->first();

                // if ($orderExists == null) {
                //     return response()->json([
                //         'code' => 404,
                //         'success' => false,
                //         'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                //         'msg' => MsgCode::NO_ORDER_EXISTS[1],
                //     ], 404);
                // }

                // $shipperArr = array(
                //     "store_id" => $request->store->id,
                //     "from_province_id" => $addressPickupExists->province,
                //     "from_district_id" => $addressPickupExists->district,
                //     "from_wards_id" => $addressPickupExists->wards,
                //     "to_province_id" => $orderExists->customer_province,
                //     "to_district_id" => $orderExists->customer_wards,
                //     "to_wards_id" => $orderExists->customer_wards,
                //     "to_address_detail" => $orderExists->customer_address_detail,
                //     "customer_name" => $orderExists->customer_name,
                //     "service_type" => $orderExists->shipper_type,
                //     "weight" => $orderExists->package_weight,
                //     "length" => $orderExists->package_length,
                //     "width" => $orderExists->package_width,
                //     "height" => $orderExists->package_height,
                // );
            } else if ($request->customer == null) {
                $shipperArr = array(
                    "store_id" => $request->store->id,
                    "from_province_id" => $addressPickupExists->province,
                    "from_district_id" => $addressPickupExists->district,
                    "from_wards_id" => $addressPickupExists->wards,
                    "to_province_id" => $request->province_id,
                    "to_district_id" => $request->district_id,
                    "to_wards_id" => $request->wards_id,
                    "to_address_detail" => $request->address_detail,
                    "customer_name" => $request->customer_name,
                    "service_type" => $request->service_type,
                    "weight" => $package_weight,
                    "length" => $request->length,
                    "width" => $request->width,
                    "height" => $request->height,
                );
            } else {
                $addressCustomerExists = CustomerAddress::where(
                    'store_id',
                    $request->store->id
                )
                    ->where(
                        'customer_id',
                        $request->customer->id
                    )
                    ->where('id',  $request->id_address_customer)->first();

                if (empty($addressCustomerExists)) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_ADDRESS_EXISTS[0],
                        'msg' => MsgCode::NO_ADDRESS_EXISTS[1],
                    ], 404);
                }
                $shipperArr = array(
                    "store_id" => $request->store->id,
                    "from_province_id" => $addressPickupExists->province,
                    "from_district_id" => $addressPickupExists->district,
                    "from_wards_id" => $addressPickupExists->wards,
                    "to_province_id" => $addressCustomerExists->province,
                    "to_district_id" => $addressCustomerExists->district,
                    "to_wards_id" => $addressCustomerExists->wards,
                    "to_address_detail" => $addressCustomerExists->address_detail,
                    "customer_name" => $addressCustomerExists->name,
                    "service_type" => $request->service_type,
                    "weight" => $package_weight,
                    "length" => $request->length,
                    "width" => $request->width,
                    "height" => $request->height,
                );
            }

            if (!$request->user && !$request->staff) {

                $shipperArr['weight'] = $package_weight;
                $shipperArr['total_final'] = $total_final;
            }


            $partnerExists = Shipment::where('store_id', $request->store->id)
                ->where('partner_id',  $partner_id)
                ->where('use', true)
                ->whereNotNull('token')
                ->first();

            if (empty($partnerExists)) {
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'data' => [],
                ], 200);
            }

            $datas = config('saha.shipper.list_shipper');

            //Check tồn tại ID
            $listIDShip = [];
            foreach ($datas as $shiper) {
                $listIDShip[$shiper['id']] = $shiper;
            }

            if ($partner_id !== 'null' && $partner_id == 0) {

                $data = ShipperService::get_list_price_and_type_ghtk($shipperArr, null, $partnerExists->token);
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => [
                        "partner_id" =>  0,
                        "name" => $listIDShip[$partner_id]['name'],
                        'fee_with_type_ship' => $data,
                    ],
                ], 200);
            }

            if ($partner_id == 1) {
                $data = ShipperService::get_list_price_and_type_ghn($shipperArr, null, $partnerExists->token);
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => [
                        "partner_id" =>  1,
                        "name" => $listIDShip[$partner_id]['name'],
                        'fee_with_type_ship' => $data,
                    ],
                ], 200);
            }

            if ($partner_id == 2) {
                $data = ShipperService::get_list_price_and_type_viettel($shipperArr, null, $partnerExists->token);

                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => [
                        "partner_id" =>  2,
                        "name" => $listIDShip[$partner_id]['name'],
                        'fee_with_type_ship' => $data,
                    ],
                ], 200);
            }

            if ($partner_id == 3) {
                $data = ShipperService::get_list_price_and_type_vietnam_post($shipperArr, null, $partnerExists->token);
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => [
                        "partner_id" =>  3,
                        "name" => $listIDShip[$partner_id]['name'] ?? "",
                        'fee_with_type_ship' => $data,
                    ],
                ], 200);
            }

            if ($partner_id == 4) {
                $data = ShipperService::get_list_price_and_type_nhattin_post($shipperArr, null, $partnerExists->token);
                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                    'data' => [
                        "partner_id" =>  4,
                        "name" => $listIDShip[$partner_id]['name'],
                        'fee_with_type_ship' => $data,
                    ],
                ], 200);
            }


            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => [],
            ], 200);
        }
    }

    /**
     * Tính phí vận chuyển
     * @bodyParam id_address_customer integer required Id địa chỉ giao hàng
     * @bodyParam service_type integer required Kiểu giao (siêu tốc hoặc chậm)
     * @bodyParam province_id integer required Id tỉnh (ko login)
     * @bodyParam district_id integer required Id quận (ko login)
     * @bodyParam wards_id integer required Id huyện (ko login)
     * 
     */
    public function calculate_fee(Request $request)
    {
        $branch_id = null;
        if ($request->branch_id) {
            $branch_id = $request->branch_id;
        } else if ($request->branch != null) {
            $branch_id = $request->branch->id;
        } else {
            $branch_id = BranchUtils::getBranchDefaultOrderOnline($request->store->id)->id;
        }

        $package_weight = 0;
        $total_final = 0;
        $allCart = CustomerCartController::all_items_cart($request);
        $data =  CustomerCartController::data_response($allCart, $request);
        foreach ($allCart as $lineItem) {

            //tính khối lượng
            $package_weight = $package_weight + (($lineItem->product->weight <= 0 ? 100 : $lineItem->product->weight) * $lineItem->quantity);
            $total_final = $total_final + (($lineItem->item_price <= 0 ? 100 : $lineItem->item_price) * $lineItem->quantity);
        }
        if ($package_weight <= 0) {
            $package_weight = 100;
        }


        // * @bodyParam length integer required Chiều dài của gói hàng, đơn vị sử dụng cm
        // * @bodyParam width integer required Chiều rộng của gói hàng, đơn vị sử dụng cm
        // * @bodyParam height integer required Chiều cao của gói hàng, đơn vị sử dụng cm


        $config = ConfigShipController::defaultDataConfigShip($request->store->id);


        if ($config == null) {

            $data = [
                'info' => "Không tính phí ship",
                'data' => []
            ];

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $data,
            ], 200);
        }

        if (is_array($config)) {
            $config = json_decode(json_encode($config), false);
        }

        if ($config == null || $config->is_calculate_ship == false || ($config->use_fee_from_partnership == false && $config->use_fee_from_default == false)) {


            $data = [
                'info' => "Không tính phí ship",
                'data' => []
            ];

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $data,
            ], 200);
        } else {
            $addressCustomerExists = CustomerAddress::where(
                'store_id',
                $request->store->id
            )
                ->where(
                    'customer_id',
                    $request->customer == null ? null : $request->customer->id
                )
                ->where('id',  $request->id_address_customer)->first();

            $dataRes = array();

            if ($config->use_fee_from_default == true) {

                array_push($dataRes, [
                    "partner_id" =>  -1,
                    "fee" => in_array(
                        $addressCustomerExists == null ? $request->province_id : $addressCustomerExists->province,
                        $config->urban_list_id_province
                    ) ? $config->fee_urban :   $config->fee_suburban,
                    "name" => "Phí giao hàng mặc định",
                    "description" => $config->fee_default_description  ?? "",
                    "ship_type" => 0
                ]);
            }

            if ($config->use_fee_from_partnership == true) {
                $addressPickupExists = StoreAddress::where(
                    'store_id',
                    $request->store->id
                )
                    ->when($branch_id != null, function ($query)  use ($branch_id) {
                        $query->where('branch_id', $branch_id);
                    })
                    ->where('is_default_pickup', true)->first();

                if (empty($addressPickupExists)) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'data' => [],
                        'msg_code' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[0],
                        'msg' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[1],
                    ], 200);
                }
                if ($request->user != null) {

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

                    $shipperArr = array(
                        "store_id" => $request->store->id,
                        "from_province_id" => $addressPickupExists->province,
                        "from_district_id" => $addressPickupExists->district,
                        "from_wards_id" => $addressPickupExists->wards,
                        "to_province_id" => $orderExists->customer_province,
                        "to_district_id" => $orderExists->customer_wards,
                        "to_wards_id" => $orderExists->customer_wards,
                        "service_type" => $orderExists->shipper_type,
                        "weight" => $orderExists->package_weight,
                        "length" => $orderExists->package_length,
                        "width" => $orderExists->package_width,
                        "height" => $orderExists->package_height,
                    );
                } else if ($request->customer == null) {
                    $shipperArr = array(
                        "store_id" => $request->store->id,
                        "from_province_id" => $addressPickupExists->province,
                        "from_district_id" => $addressPickupExists->district,
                        "from_wards_id" => $addressPickupExists->wards,
                        "to_province_id" => $request->province_id,
                        "to_district_id" => $request->district_id,
                        "to_wards_id" => $request->wards_id,
                        "service_type" => $request->service_type,
                        "weight" => $package_weight,
                        "length" => $request->length,
                        "width" => $request->width,
                        "height" => $request->height,
                    );
                } else {
                    $addressCustomerExists = CustomerAddress::where(
                        'store_id',
                        $request->store->id
                    )
                        ->where(
                            'customer_id',
                            $request->customer->id
                        )
                        ->where('id',  $request->id_address_customer)->first();

                    if (empty($addressCustomerExists)) {
                        return response()->json([
                            'code' => 404,
                            'success' => false,
                            'msg_code' => MsgCode::NO_ADDRESS_EXISTS[0],
                            'msg' => MsgCode::NO_ADDRESS_EXISTS[1],
                        ], 404);
                    }

                    $shipperArr = array(
                        "store_id" => $request->store->id,
                        "from_province_id" => $addressPickupExists->province,
                        "from_district_id" => $addressPickupExists->district,
                        "from_wards_id" => $addressPickupExists->wards,
                        "to_province_id" => $addressCustomerExists->province,
                        "to_district_id" => $addressCustomerExists->district,
                        "to_wards_id" => $addressCustomerExists->wards,
                        "service_type" => $request->service_type,
                        "weight" => $package_weight,
                        "length" => $request->length,
                        "width" => $request->width,
                        "height" => $request->height,
                    );
                }
                $shipperArr['weight'] = $package_weight;
                $shipperArr['total_final'] = $total_final;

                $data = ShipperService::caculate_monney_all($shipperArr);
                $dataRes = array_merge($dataRes, $data['data']);
            }



            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => [
                    'info' => "",
                    "data" => $dataRes
                ],
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 200);
        }
    }
}
