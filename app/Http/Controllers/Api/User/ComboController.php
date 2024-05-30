<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductCombo;

use DateTime;
use Illuminate\Http\Request;

/**
 * @group  User/Chương trình khuyến mãi
 * discount_type // 0 gia co dinh - 1 theo phan tram
 * set_limit_total khi set giá trị true - yêu cầu khách hàng phải mua đủ sản phẩm
 */

class ComboController extends Controller
{

    /**
     * Tạo combo mới
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
     
     * @bodyParam discount_type int required  0 giám giá cố định - 1 theo %
     * @bodyParam value_discount float required (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
     
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam combo_products List<json> required danh sách sản phẩm kèm số lượng [ {product_id:1, quantity: 10} ]
     * 
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * @bodyParam group_customers array required danh sách id của nhóm áp dụng VD: [0,1,2]
     * @bodyParam group_types array required VD: group_types => [{id: 1, name: Sỉ lẻ}]
     * @bodyParam agency_types array required VD: agency_types => [{id: 1, name: Cấp 1}]
     */


    public function create(Request $request)
    {
        $comboItemRequests = $request->combo_products;
        $comboItems = [];

        //Add line item
        if ($comboItemRequests != null && is_array($comboItemRequests)) {
            foreach ($comboItemRequests as $comboItemRequest) {

                $product_id = $comboItemRequest["product_id"];
                $quantity = $comboItemRequest["quantity"];

                if (
                    !isset($product_id) || !isset($quantity)
                    || $quantity <= 0
                ) {

                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PRODUCT_ITEM[0],
                        'msg' => MsgCode::INVALID_PRODUCT_ITEM[1],
                    ], 400);
                }

                $checkProductExists = Product::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'id',
                    $product_id
                )->first();

                if ($checkProductExists == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0] . "|" . $product_id,
                        'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                    ], 400);
                }

                array_push($comboItems, (object)[
                    'product_id' => $product_id,
                    'quantity' => $quantity
                ]);
            }
        }


        if (count($comboItems) < 2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NUMBER_OF_COMBOS_MUST_BE_MORE_THAN_2[0],
                'msg' => MsgCode::NUMBER_OF_COMBOS_MUST_BE_MORE_THAN_2[1],
            ], 400);
        }

        //Check duplicate
        foreach ($comboItems as $item) {
            $dup = 0;
            foreach ($comboItems as $item2) {
                if ($item->product_id == $item2->product_id) {
                    $dup++;

                    if ($dup == 2) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::DUPLICATE_PRODUCT[0],
                            'msg' => MsgCode::DUPLICATE_PRODUCT[1],
                        ], 400);
                    }
                }
            }
        }


        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->start_time == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::START_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::START_TIME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->end_time == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 400);
        }

        $now = Helper::getTimeNowDateTime();

        $d2 = new DateTime($request->end_time);
        if ($now > $d2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }

        if ($request->discount_type != 1 && $request->discount_type != 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_COMBO_DISCOUNT_TYPE[0],
                'msg' => MsgCode::INVALID_COMBO_DISCOUNT_TYPE[1],
            ], 400);
        }

        if ($request->value_discount == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VALUE_IS_REQUIRED[0],
                'msg' => MsgCode::VALUE_IS_REQUIRED[1],
            ], 400);
        }


        $amount = null;
        $setLimit = false;
        if ($request->amount > 0 && filter_var($request->set_limit_amount, FILTER_VALIDATE_BOOLEAN) == true) {
            $amount = $request->amount;
            $setLimit = true;
        }

        $group_types = null;
        $agency_types = null;
        $group_customers = null;

        if ($request->group_customers && is_array($request->group_customers) === false) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_APPLY_FOR[0],
                'msg' => MsgCode::INVALID_TYPE_APPLY_FOR[1],
            ], 400);
        }

        if ($request->group_customers && is_array($request->group_customers)) {

            $group_customers = $request->group_customers;
        }

        if (is_array($request->group_types) && in_array(CustomerUtils::GROUP_CUSTOMER_BY_CONDITION, $request->group_customers)) {

            $group_types = $request->group_types;
        }

        if (is_array($request->agency_types) && in_array(CustomerUtils::GROUP_CUSTOMER_AGENCY, $request->group_customers)) {

            $agency_types = $request->agency_types;
        }

        $data = [
            'is_end' => false,
            'store_id' => $request->store->id,
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,

            'discount_type' => $request->discount_type,
            'value_discount' => $request->value_discount,

            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'used' => 0,

            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'group_customers' => $group_customers,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ];


        //sản phẩm

        if (count($comboItems) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        //check exist promotion
        foreach ($comboItems as $item) {

            $product_combo_ids = ProductCombo::where(
                'store_id',
                $request->store->id
            )->where(
                'product_id',
                $item->product_id
            )->get()->pluck('combo_id');


            if (count($product_combo_ids)  == 0) break;

            $combos = Combo::whereIn('id', $product_combo_ids)->get();
            if (count($combos)  == 0) break;

            // foreach ($combos  as $comboItem) {
            //     if ($comboItem != null && $comboItem->comingOrHappenning() == true) {
            //         return response()->json([
            //             'code' => 400,
            //             'success' => false,
            //             'msg_code' =>  MsgCode::PRODUCT_EXIS_IN_COMBO[0] . "|" . $item->product_id,
            //             'msg' => MsgCode::PRODUCT_EXIS_IN_COMBO[1],
            //         ], 400);
            //     }
            // }
        }

        $combo_exists = Combo::where('store_id', $request->store->id)
            ->get();


        if ($combo_exists) {
            foreach ($combo_exists  as $combo_item) {
                $product_combo_ids = ProductCombo::where('store_id', $request->store->id)
                    ->where('combo_id', $combo_item->id)
                    ->get()
                    ->pluck('product_id')
                    ->toArray();

                $new_product_ids = array_map(function ($item) {
                    return $item->product_id;
                }, $comboItems);

                if (count($product_combo_ids) > 0 && count($product_combo_ids) === count($new_product_ids) && $combo_item->comingOrHappenning() == true) {
                    $diff = array_diff($new_product_ids, $product_combo_ids);

                    if (empty($diff)) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' =>  MsgCode::PRODUCT_EXIS_IN_COMBO[0],
                            'msg' => MsgCode::PRODUCT_EXIS_IN_COMBO[1],
                        ], 400);
                    }
                }
            }
        }



        if (count($data) == 2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        $ComboCreate = Combo::create(
            $data
        );


        //Add item line
        foreach ($comboItems as $comboItem) {
            $lineItem = ProductCombo::create(
                [
                    'store_id' => $request->store->id,
                    'combo_id' =>  $ComboCreate->id,
                    'product_id' => $comboItem->product_id,
                    'quantity' => $comboItem->quantity,
                ]
            );
        }


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Thêm combo " . $ComboCreate->name,
            $ComboCreate->id,
            $ComboCreate->name
        );

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Combo::where('id', $ComboCreate->id)
                ->first()
        ], 201);
    }

    /**
     * Update combo
     * @urlParam  store_code required Store code
     * @urlParam  Combo_id required Id Combo
     * @bodyParam is_end boolean required Chương trình đã kết thúc chưa
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thời gian kết thúc

     * @bodyParam discount_type int required (combo_type == 1) 0 giám giá cố định - 1 theo %
     * @bodyParam value_discount float required (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
     * 
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam combo_products List<json> required danh sách sản phẩm kèm số lượng [ {product_id:1, quantity: 10} ]
     * 
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     */


    public function updateOneCombo(Request $request)
    {

        $id = $request->route()->parameter('combo_id');
        $checkComboExists = Combo::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkComboExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COMBO_EXISTS[0],
                'msg' => MsgCode::NO_COMBO_EXISTS[1],
            ], 400);
        }

        ///// Trường hợp kết thúc luôn
        if (filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN) == true) {
            $newData = [
                'is_end' => filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN),
            ];

            $checkComboExists->update(
                $newData
            );
            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => Combo::where('id', $checkComboExists->id)
                    ->first()
            ], 201);
        }

        $err = null;


        if ($request->name == null) {
            $err = MsgCode::NAME_IS_REQUIRED;
        }

        if ($request->start_time == null) {
            $err = MsgCode::START_TIME_IS_REQUIRED;
        }

        if ($request->end_time == null) {
            $err = MsgCode::END_TIME_IS_REQUIRED;
        }

        $now = Helper::getTimeNowDateTime();

        $d2 = new DateTime($request->end_time);
        if ($now > $d2) {
            $err = MsgCode::INVALID_TIME;
        }

        if ($request->discount_type != 1 && $request->discount_type != 0) {
            $err = MsgCode::INVALID_COMBO_DISCOUNT_TYPE;
        }

        if ($request->value_discount == null) {
            $err = MsgCode::VALUE_IS_REQUIRED;
        }



        $amount = null;
        $setLimit = false;
        if ($request->amount > 0 && filter_var($request->set_limit_amount, FILTER_VALIDATE_BOOLEAN) == true) {
            $amount = $request->amount;
            $setLimit = true;
        }


        if ($err != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => $err[0],
                'msg' => $err[1],
            ], 400);
        }


        $comboItemRequests = $request->combo_products;
        $comboItems = [];

        //Add line item
        if ($comboItemRequests != null && is_array($comboItemRequests)) {
            foreach ($comboItemRequests as $comboItemRequest) {

                $product_id = $comboItemRequest["product_id"];
                $quantity = $comboItemRequest["quantity"];

                if (
                    !isset($product_id) || !isset($quantity)
                    || $quantity <= 0
                ) {

                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PRODUCT_ITEM[0],
                        'msg' => MsgCode::INVALID_PRODUCT_ITEM[1],
                    ], 400);
                }

                $checkProductExists = Product::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'id',
                    $product_id
                )->first();

                if ($checkProductExists == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0] . "|" . $product_id,
                        'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                    ], 400);
                }

                array_push($comboItems, (object)[
                    'product_id' => $product_id,
                    'quantity' => $quantity
                ]);
            }
        }

        //Check duplicate
        foreach ($comboItems as $item) {
            $dup = 0;
            foreach ($comboItems as $item2) {
                if ($item->product_id == $item2->product_id) {
                    $dup++;

                    if ($dup == 2) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::DUPLICATE_PRODUCT[0],
                            'msg' => MsgCode::DUPLICATE_PRODUCT[1],
                        ], 400);
                    }
                }
            }
        }


        $arrProductInComboExits = $checkComboExists->products_combo()->get()->pluck('product_id')->toArray();

        //check exist promotion
        foreach ($comboItems as $item) {




            if (!in_array($item->product_id, $arrProductInComboExits)) {
                $product_combo_ids = ProductCombo::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'product_id',
                    $item->product_id
                )->get()->pluck('combo_id');




                if (count($product_combo_ids)  == 0) break;

                $combos = Combo::whereIn('id', $product_combo_ids)->where('is_end', false)
                    ->where('end_time', '>=', $now)->get();

                if (count($combos)  == 0) break;

                // foreach ($combos  as $comboItem) {

                //     if ($comboItem != null && $comboItem->comingOrHappenning() == true) {

                //         return response()->json([
                //             'code' => 400,
                //             'success' => false,
                //             'msg_code' => MsgCode::PRODUCT_EXIS_IN_COMBO[0] . "|" . $item->product_id,
                //             'msg' => MsgCode::PRODUCT_EXIS_IN_COMBO[1],
                //         ], 400);
                //     }
                // }
            }
        }


        ///////Remove 
        ProductCombo::where('combo_id', $id)->delete();

        $combo_exists = Combo::where('store_id', $request->store->id)
            ->get();

        if ($combo_exists) {
            foreach ($combo_exists  as $combo_item) {
                $product_combo_ids = ProductCombo::where('store_id', $request->store->id)
                    ->where('combo_id', $combo_item->id)
                    ->get()
                    ->pluck('product_id')
                    ->toArray();

                $new_product_ids = array_map(function ($item) {
                    return $item->product_id;
                }, $comboItems);

                if (count($product_combo_ids) > 0 && count($product_combo_ids) === count($new_product_ids) && $combo_item->comingOrHappenning() == true) {
                    $diff = array_diff($new_product_ids, $product_combo_ids);

                    if (empty($diff)) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' =>  MsgCode::PRODUCT_EXIS_IN_COMBO[0],
                            'msg' => MsgCode::PRODUCT_EXIS_IN_COMBO[1],
                        ], 400);
                    }
                }
            }
        }


        //  ProductCombo::whereIn('product_id', $productIds)->delete();
        ///////================================

        $group_types = $checkComboExists->group_types;
        $agency_types = $checkComboExists->agency_types;
        $group_customers = $checkComboExists->group_customers;

        if ($request->group_customers && is_array($request->group_customers) === false) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_APPLY_FOR[0],
                'msg' => MsgCode::INVALID_TYPE_APPLY_FOR[1],
            ], 400);
        }

        if ($request->group_customers && is_array($request->group_customers)) {

            $group_customers = $request->group_customers;
        }

        if (is_array($request->group_types) && in_array(CustomerUtils::GROUP_CUSTOMER_BY_CONDITION, $request->group_customers)) {

            $group_types = $request->group_types;
        } else {

            $group_types = null;
        }

        if (is_array($request->agency_types) && in_array(CustomerUtils::GROUP_CUSTOMER_AGENCY, $request->group_customers)) {

            $agency_types = $request->agency_types;
        } else {

            $agency_types = null;
        }

        $newData = [
            'is_end' => filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN),
            'store_id' => $request->store->id,
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'discount_type' => $request->discount_type,
            'value_discount' => $request->value_discount,
            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'used' => $request->amount != $checkComboExists->amount ? 0 :   $checkComboExists->used,

            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'group_customers' => $group_customers,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ];

        $checkComboExists->update(
            $newData
        );

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Cập nhật combo " . $checkComboExists->name,
            $checkComboExists->id,
            $checkComboExists->name
        );


        //Add item line
        foreach ($comboItems as $comboItem) {

            $lineItem = ProductCombo::create(
                [
                    'store_id' => $request->store->id,
                    'combo_id' =>  $checkComboExists->id,
                    'product_id' => $comboItem->product_id,
                    'quantity' => $comboItem->quantity,
                ]
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Combo::where('id', $checkComboExists->id)
                ->first()
        ], 201);
    }

    /**
     * Xem tất cả combo chuẩn vị và đang phát hàng
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request, $id)
    {
        $now = Helper::getTimeNowDateTime();
        $combos = Combo::where('store_id', $request->store->id,)
            ->where('is_end', false)
            ->where('end_time', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $combos,
        ], 200);
    }

    /**
     * Xem 1 combo
     * @urlParam  store_code required Store code
     * @urlParam  combo_id required Id Combo
     */
    public function getOneCombo(Request $request)
    {

        $id = $request->route()->parameter('combo_id');
        $checkComboExists = Combo::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkComboExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_COMBO_EXISTS[0],
                'msg' => MsgCode::NO_COMBO_EXISTS[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkComboExists,
        ], 200);
    }

    /**
     * Xem tất cả Combo đã kết thúc
     * @queryParam  page Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)
     * @urlParam  store_code required Store code
     */
    public function getAllEnd(Request $request, $id)
    {
        $now = Helper::getTimeNowDateTime();
        $Combos = Combo::where('store_id', $request->store->id)
            ->where(function ($query) use ($now) {
                $query->where('is_end', '=', true)
                    ->orWhere('end_time', '<', $now);
            })
            ->orderBy('end_time', 'desc')
            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $Combos,
        ], 200);
    }

    /**
     * xóa một chương trình Combo
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID combo cần xóa thông tin.
     */
    public function deleteOneCombo(Request $request)
    {
        $id = $request->route()->parameter('combo_id');
        $comboExists = Combo::where('id', $id)->first();

        if (empty($comboExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_COMBO_EXISTS[0],
                'msg' => MsgCode::NO_COMBO_EXISTS[1],
            ], 404);
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Xóa combo " . $comboExists->name,
            $comboExists->id,
            $comboExists->name
        );

        $comboExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $comboExists->id],
        ], 200);
    }
}
