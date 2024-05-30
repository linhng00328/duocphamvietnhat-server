<?php

namespace App\Helper;

use App\Models\CustomerVoucher;
use App\Models\MsgCode;
use App\Models\Voucher;
use App\Models\VoucherCode;

class VoucherUtils
{

    static function data_voucher_discount_for_0($codeVoucher, $allCart, $request, $total_after_discount)
    {
        $ship_discount_value = 0;
        $is_free_ship = true;
        $voucher_discount_amount = 0;
        $now = Helper::getTimeNowString();
        $discount_for = 0;
        $used_voucher = null;
        //Tinh giam gia voucher
        if (!empty($codeVoucher)) {
            $voucher = Voucher::where('store_id', $request->store->id,)
                ->where('is_end', false)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('code', $codeVoucher)
                ->first();
            $voucherExists = null;
            $voucherCode = null;

            $request = request();
            $customer = request('customer', $default = null);
            $ok_customer = false;

            $is_use_once = false;
            $is_use_once_code_multiple_time = true;
            if (!empty($voucher)) {
                if ($voucher->is_use_once == true) {
                    $customerVoucher = CustomerVoucher::where('store_id', $request->store->id)
                        ->where('customer_id', $customer ? $customer->id : null)
                        ->where('voucher_id', $voucher->id)
                        ->first();

                    if ($customerVoucher) $is_use_once = true;
                }

                if ($is_use_once === false) {
                    $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                        $request,
                        $voucher->group_customer,
                        $voucher->agency_type_id,
                        $voucher->group_type_id,
                        $customer,
                        $request->store->id,
                        $voucher->group_customers,
                        $voucher->agency_types,
                        $voucher->group_types
                    );
                }
            } else {
                $voucherCode = VoucherCode::where('store_id', $request->store->id)
                    ->where('start_time', '<=', $now)
                    ->where('end_time', '>=', $now)
                    ->where('status', 0)
                    ->where('code', $codeVoucher)
                    ->first();

                if ($voucherCode) {
                    $voucherExists = $voucherCode->voucher()->first();
                    if ($voucherExists) {
                        $is_use_once_code_multiple_time = false;
                        $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                            $request,
                            $voucherExists->group_customer,
                            $voucherExists->agency_type_id,
                            $voucherExists->group_type_id,
                            $customer,
                            $request->store->id,
                            $voucherExists->group_customers,
                            $voucherExists->agency_types,
                            $voucherExists->group_types
                        );
                    }
                }
            }

            if ($is_use_once) {
                return [
                    'msg_code' => MsgCode::VOUCHER_ONLY_APPLIED_ONCE
                ];
            } else if (($is_use_once_code_multiple_time && empty($voucher)) || !$ok_customer) {
                return [
                    'msg_code' => MsgCode::NO_VOUCHER_EXISTS
                ];
            }
            if ($is_use_once_code_multiple_time && $voucher->amount - $voucher->used <= 0 && $voucher->set_limit_amount == true) {
                return [
                    'msg_code' => MsgCode::VOUCHERS_ARE_SOLD_OUT
                ];
            }

            $listIdProduct = [];
            foreach ($allCart as $lineItem) {
                $listIdProduct[$lineItem->product->id] = [
                    "id"  => $lineItem->product->id,
                    "quantity" => $lineItem->quantity,
                    "price_or_discount" => $lineItem->item_price
                ];
            }
            if (!empty($voucher) || $voucherExists) {
                if ($voucherExists) {
                    $voucher = $voucherExists;
                }

                $totalAvalibleForVoucher = 0;
                if ($voucher->voucher_type == 0) { //tat ca san pham


                    foreach ($allCart as $lineItem) {

                        $totalAvalibleForVoucher += $lineItem->item_price * $lineItem->quantity;
                    }
                } else { //mot so san pham trong voucher
                    $product_ids = $voucher->product_voucher()->pluck('product_id')->toArray();
                    $product_in_voucher = [];

                    foreach ($product_ids as $product_id) {
                        $product_in_voucher[$product_id] = true;
                    }

                    foreach ($allCart as $lineItem) {
                        if (isset($product_in_voucher[$lineItem->product->id])) {



                            $totalAvalibleForVoucher += $lineItem->item_price * $lineItem->quantity;
                        }
                    }
                }


                if (($voucher->set_limit_total == true && $totalAvalibleForVoucher >= $voucher->value_limit_total) ||
                    $voucher->set_limit_total == false
                ) {


                    if ($voucher->discount_for == 1) {

                        $discount_for = 1;
                        $used_voucher = $voucher;
                        if ($voucher->is_free_ship == true) { //tat ca san pham

                            $is_free_ship = true;
                        } else { //mot so san pham trong voucher

                            $is_free_ship = false;
                            $ship_discount_value = $voucher->ship_discount_value;
                        }
                    } else {
                        $discount_for = 0;

                        $used_voucher = $voucher;

                        // $product_in_voucher = [];
                        // foreach ($voucher->products as $product) {
                        //     $product_in_voucher[$product->id] = true;
                        // }




                        $used_voucher = $voucher;

                        if ($voucher->discount_type == 0) {
                            $voucher_discount_amount = $voucher->value_discount;
                        }
                        if ($voucher->discount_type == 1) {

                            $totalDiscounnt = $totalAvalibleForVoucher  * ($voucher->value_discount / 100);

                            if ($totalDiscounnt > $voucher->max_value_discount && $voucher->set_limit_value_discount == true) {
                                $totalDiscounnt = $voucher->max_value_discount;
                            }
                            $voucher_discount_amount = $totalDiscounnt;
                        }
                    }
                    //-----chốt giảm voucher
                    $total_after_discount = $total_after_discount - $voucher_discount_amount < 0 ? 0 :  $total_after_discount - $voucher_discount_amount;
                } else {
                    return [
                        'msg_code' => MsgCode::NOT_ENOUGH_USE_VOUCHER
                    ];
                }
            } else {
                return [
                    'msg_code' => MsgCode::NO_VOUCHER_EXISTS
                ];
            }

            $used_voucher = $used_voucher ? $used_voucher->toArray() : [];
            if ($voucherCode) {
                $used_voucher['voucher_code'] = $voucherCode;
            }
            return [
                'voucher_discount_amount' =>  $voucher_discount_amount,
                'used_voucher'  => $used_voucher,
                'ship_discount_value' => $ship_discount_value,
                'is_free_ship' => $is_free_ship,
                'discount_for' => $discount_for,
            ];
        }
    }

    static function data_voucher_discount_for_0V1($codeVoucher, $allCart, $request, $total_after_discount)
    {
        $ship_discount_value = 0;
        $is_free_ship = true;
        $voucher_discount_amount = 0;
        $now = Helper::getTimeNowString();
        $discount_for = 0;
        $used_voucher = null;
        //Tinh giam gia voucher
        if (!empty($codeVoucher)) {
            $voucher = Voucher::where('store_id', $request->store->id,)
                ->where('is_end', false)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('code', $codeVoucher)
                ->first();
            $voucherExists = null;
            $voucherCode = null;

            $request = request();
            $customer = request('customer', $default = null);
            $ok_customer = false;

            $is_use_once = false;
            $is_use_once_code_multiple_time = true;
            if (!empty($voucher)) {
                if ($voucher->is_use_once == true) {
                    $customerVoucher = CustomerVoucher::where('store_id', $request->store->id)
                        ->where('customer_id', $customer ? $customer->id : null)
                        ->where('voucher_id', $voucher->id)
                        ->first();

                    if ($customerVoucher) $is_use_once = true;
                }

                if ($is_use_once === false) {
                    $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                        $request,
                        $voucher->group_customer,
                        $voucher->agency_type_id,
                        $voucher->group_type_id,
                        $customer,
                        $request->store->id,
                        $voucher->group_customers,
                        $voucher->agency_types,
                        $voucher->group_types
                    );
                }
            } else {
                $voucherCode = VoucherCode::where('store_id', $request->store->id)
                    ->where('start_time', '<=', $now)
                    ->where('end_time', '>=', $now)
                    ->where('status', 0)
                    ->where('code', $codeVoucher)
                    ->first();

                if ($voucherCode) {
                    $voucherExists = $voucherCode->voucher()->first();
                    if ($voucherExists) {
                        $is_use_once_code_multiple_time = false;
                        $ok_customer = GroupCustomerUtils::check_valid_ok_customer(
                            $request,
                            $voucherExists->group_customer,
                            $voucherExists->agency_type_id,
                            $voucherExists->group_type_id,
                            $customer,
                            $request->store->id,
                            $voucherExists->group_customers,
                            $voucherExists->agency_types,
                            $voucherExists->group_types
                        );
                    }
                }
            }

            if ($is_use_once) {
                return [
                    'msg_code' => MsgCode::VOUCHER_ONLY_APPLIED_ONCE
                ];
            } else if (($is_use_once_code_multiple_time && empty($voucher)) || !$ok_customer) {
                return [
                    'msg_code' => MsgCode::NO_VOUCHER_EXISTS
                ];
            }
            if ($is_use_once_code_multiple_time && $voucher->amount - $voucher->used <= 0 && $voucher->set_limit_amount == true) {
                return [
                    'msg_code' => MsgCode::VOUCHERS_ARE_SOLD_OUT
                ];
            }

            $listIdProduct = [];
            foreach ($allCart as $lineItem) {
                $listIdProduct[$lineItem['product']['id']] = [
                    "id"  => $lineItem['product']['id'],
                    "quantity" => $lineItem['quantity'],
                    "price_or_discount" => $lineItem['item_price']
                ];
            }
            if (!empty($voucher) || $voucherExists) {
                if ($voucherExists) {
                    $voucher = $voucherExists;
                }

                $totalAvalibleForVoucher = 0;
                if ($voucher->voucher_type == 0) { //tat ca san pham


                    foreach ($allCart as $lineItem) {

                        $totalAvalibleForVoucher += $lineItem['item_price'] * $lineItem['quantity'];
                    }
                } else { //mot so san pham trong voucher
                    $product_ids = $voucher->product_voucher()->pluck('product_id')->toArray();
                    $product_in_voucher = [];

                    foreach ($product_ids as $product_id) {
                        $product_in_voucher[$product_id] = true;
                    }

                    foreach ($allCart as $lineItem) {
                        if (isset($product_in_voucher[$lineItem['product']['id']])) {



                            $totalAvalibleForVoucher += $lineItem['item_price'] * $lineItem['quantity'];
                        }
                    }
                }


                if (($voucher->set_limit_total == true && $totalAvalibleForVoucher >= $voucher->value_limit_total) ||
                    $voucher->set_limit_total == false
                ) {


                    if ($voucher->discount_for == 1) {

                        $discount_for = 1;
                        $used_voucher = $voucher;
                        if ($voucher->is_free_ship == true) { //tat ca san pham

                            $is_free_ship = true;
                        } else { //mot so san pham trong voucher

                            $is_free_ship = false;
                            $ship_discount_value = $voucher->ship_discount_value;
                        }
                    } else {
                        $discount_for = 0;

                        $used_voucher = $voucher;

                        // $product_in_voucher = [];
                        // foreach ($voucher->products as $product) {
                        //     $product_in_voucher[$product->id] = true;
                        // }




                        $used_voucher = $voucher;

                        if ($voucher->discount_type == 0) {
                            $voucher_discount_amount = $voucher->value_discount;
                        }
                        if ($voucher->discount_type == 1) {

                            $totalDiscounnt = $totalAvalibleForVoucher  * ($voucher->value_discount / 100);

                            if ($totalDiscounnt > $voucher->max_value_discount && $voucher->set_limit_value_discount == true) {
                                $totalDiscounnt = $voucher->max_value_discount;
                            }
                            $voucher_discount_amount = $totalDiscounnt;
                        }
                    }
                    //-----chốt giảm voucher
                    $total_after_discount = $total_after_discount - $voucher_discount_amount < 0 ? 0 :  $total_after_discount - $voucher_discount_amount;
                } else {
                    return [
                        'msg_code' => MsgCode::NOT_ENOUGH_USE_VOUCHER
                    ];
                }
            } else {
                return [
                    'msg_code' => MsgCode::NO_VOUCHER_EXISTS
                ];
            }

            $used_voucher = $used_voucher ? $used_voucher->toArray() : [];
            if ($voucherCode) {
                $used_voucher['voucher_code'] = $voucherCode;
            }
            return [
                'voucher_discount_amount' =>  $voucher_discount_amount,
                'used_voucher'  => $used_voucher,
                'ship_discount_value' => $ship_discount_value,
                'is_free_ship' => $is_free_ship,
                'discount_for' => $discount_for,
            ];
        }
    }
}
