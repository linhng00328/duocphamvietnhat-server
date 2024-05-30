<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\ProductUtils;
use App\Http\Controllers\Controller;
use App\Models\BonusProduct;
use App\Models\BonusProductItem;
use App\Models\BonusProductItemLadder;
use App\Models\Combo;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductCombo;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

/**
 * @group  User/Chương trình khuyến mãi tặng thưởng sản phẩm
 * 
 */

class BonusProductController extends Controller
{

    function has_dupes($array)
    {
        $dupe_array = array();
        foreach ($array as $val) {
            if (++$dupe_array[$val] > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Tạo tặng thưởng mới
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required Thời gian kết thúc
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * 
     * @bodyParam ladder_reward bool required có phải khuyến mãi tầng ko
     * @bodyParam data_ladder { product_id,distribute_name,element_distribute_name,sub_element_distribute_name, , list:[{from_quantity, bonus_quantity, bo_product_id, bo_element_distribute_name, bo_sub_element_distribute_name}] }

     * @bodyParam select_products List<json> required danh sách sản phẩm mua và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1, quantity: 10} ]
     * @bodyParam bonus_products List<json> required danh sách sản phẩm được tặng và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1,, quantity: 10} ]
     * @bodyParam allows_choose_distribute boolean required thêm cái này vào ds nếu cho phép chọn phân loại sp thưởng
     * @bodyParam allows_all_distribute boolean required thêm cái này vào ds nếu cho phép tất cả phân loại được thưởng
     * @bodyParam multiply_by_number bool nhan theo so luong
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



        $ladder_reward =  filter_var($request->ladder_reward, FILTER_VALIDATE_BOOLEAN);


        if ($ladder_reward  == false) {

            $select_products = $request->select_products;
            if ($select_products  == null || count($select_products) == 0) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Thêm sản phẩm thưởng",
                ], 404);
            }


            //Check product select
            foreach ($select_products  as $select_product) {
                $product_id = $select_product['product_id'];

                $distribute_name = $select_product['distribute_name'] ?? null;
                $element_distribute_name = $select_product['element_distribute_name'] ?? null;
                $sub_element_distribute_name = $select_product['sub_element_distribute_name'] ?? null;
                $allows_all_distribute = $select_product['allows_all_distribute'] ?? null;


                $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
                    ->first();

                if ($productExist == null) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                        'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                    ], 404);
                }


                if ($allows_all_distribute == true) {

                    $bonus_product_ids = BonusProductItem::where(
                        'store_id',
                        $request->store->id
                    )->where(
                        'product_id',
                        $product_id
                    )->where('allows_all_distribute', true)
                        ->where(
                            'is_select_product',
                            true
                        )
                        ->get()->pluck('bonus_product_id');


                    // if (count($bonus_product_ids) > 0) {
                    //     $bonus_products = BonusProduct::whereIn('id', $bonus_product_ids)->get();
                    //     if (count($bonus_products)  > 0) {
                    //         foreach ($bonus_products  as $bonus_product) {
                    //             if ($bonus_product != null && $bonus_product->comingOrHappenning() == true) {
                    //                 return response()->json([
                    //                     'code' => 400,
                    //                     'success' => false,
                    //                     'msg_code' =>  MsgCode::PRODUCT_EXIS_IN_BONUS_PRODUCT[0] . "|" . $product_id,
                    //                     'msg' => MsgCode::PRODUCT_EXIS_IN_BONUS_PRODUCT[1],
                    //                 ], 400);
                    //             }
                    //         }
                    //     }
                    // }
                } else {
                    $status_stock =  ProductUtils::get_id_distribute_and_stock(
                        $request->store->id,
                        null,
                        $product_id,
                        $distribute_name,
                        $element_distribute_name,
                        $sub_element_distribute_name
                    );

                    if ($status_stock == null || ProductUtils::check_type_distribute($productExist) != $status_stock['type']) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::VERSION_NOT_FOUND_PRODUCT[0],
                            'msg' => MsgCode::VERSION_NOT_FOUND_PRODUCT[1],
                        ], 400);
                    }

                    $bonus_product_ids = BonusProductItem::where(
                        'store_id',
                        $request->store->id
                    )->where(
                        'product_id',
                        $product_id
                    )
                        ->where(
                            'is_select_product',
                            true
                        )->where(
                            'element_distribute_id',
                            $status_stock['element_distribute_id']  ?? null
                        )->where(
                            'sub_element_distribute_id',
                            $status_stock['sub_element_distribute_id']  ?? null
                        )
                        ->get()->pluck('bonus_product_id');


                    if (count($bonus_product_ids) > 0) {
                        // $bonus_products = BonusProduct::whereIn('id', $bonus_product_ids)->get();
                        // if (count($bonus_products)  > 0) {
                        //     foreach ($bonus_products  as $bonus_product) {
                        //         if ($bonus_product != null && $bonus_product->comingOrHappenning() == true) {
                        //             return response()->json([
                        //                 'code' => 400,
                        //                 'success' => false,
                        //                 'msg_code' =>  MsgCode::PRODUCT_EXIS_IN_BONUS_PRODUCT[0] . "|" . $product_id,
                        //                 'msg' => MsgCode::PRODUCT_EXIS_IN_BONUS_PRODUCT[1],
                        //             ], 400);
                        //         }
                        //     }
                        // }
                    }
                }
            }
            //
            $bonus_products = $request->bonus_products;

            if ($bonus_products  == null || count($bonus_products) == 0) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Thêm sản phẩm thưởng",
                ], 404);
            }
            //Check product select
            foreach ($bonus_products  as $bonus_product) {
                $product_id = $bonus_product['product_id'];

                $distribute_name = $bonus_product['distribute_name'] ?? null;
                $element_distribute_name = $bonus_product['element_distribute_name'] ?? null;
                $sub_element_distribute_name = $bonus_product['sub_element_distribute_name'] ?? null;

                $allows_choose_distribute  = $bonus_product['allows_choose_distribute'] ?? false;

                $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
                    ->first();

                if ($productExist == null) {
                    return response()->json([
                        'code' => 404,
                        'success' => false,
                        'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                        'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                    ], 404);
                }

                if ($allows_choose_distribute == true) {
                } else {

                    $status_stock =  ProductUtils::get_id_distribute_and_stock(
                        $request->store->id,
                        null,
                        $product_id,
                        $distribute_name,
                        $element_distribute_name,
                        $sub_element_distribute_name
                    );

                    if ($status_stock == null || ProductUtils::check_type_distribute($productExist) != $status_stock['type']) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::VERSION_NOT_FOUND_BONUS_PRODUCT[0],
                            'msg' => MsgCode::VERSION_NOT_FOUND_BONUS_PRODUCT[1],
                        ], 400);
                    }
                }
            }
        } else { //khuyến mãi tầng



        }



        //// //// //// //// //// //// //// //// //// //// //// //// //// ////


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

            $agency_types = $request->agency_typres;
        }

        $data = [
            'is_end' => false,
            'store_id' => $request->store->id,
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'multiply_by_number' => $request->multiply_by_number,

            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'used' => 0,
            "ladder_reward" =>  $ladder_reward,

            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'group_customers' => $group_customers,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ];


        $bonusProductCreate = BonusProduct::create(
            $data
        );


        if ($ladder_reward  == false) {
            //Add item line select
            foreach ($select_products as $select_product) {
                $product_id = $select_product['product_id'];

                $distribute_name = $select_product['distribute_name']  ?? null;
                $element_distribute_name = $select_product['element_distribute_name']  ?? null;
                $sub_element_distribute_name = $select_product['sub_element_distribute_name']  ?? null;
                $allows_all_distribute = $select_product['allows_all_distribute']  ?? null;

                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                $lineItem = BonusProductItem::create(
                    [
                        'store_id' => $request->store->id,
                        'bonus_product_id' =>  $bonusProductCreate->id,
                        'product_id' =>   $product_id,
                        'is_select_product' => true,
                        'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                        'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,
                        "distribute_name"  =>  $distribute_name,
                        'element_distribute_name' => $element_distribute_name,
                        'sub_element_distribute_name' => $sub_element_distribute_name,
                        'quantity' => $select_product['quantity'] ?? 1,
                        'allows_all_distribute' =>   $allows_all_distribute
                    ]
                );
            }

            //Add item line bonus
            foreach ($bonus_products as $bonus_product) {
                $product_id = $bonus_product['product_id'];

                $distribute_name = $bonus_product['distribute_name']  ?? null;
                $element_distribute_name = $bonus_product['element_distribute_name']  ?? null;
                $sub_element_distribute_name = $bonus_product['sub_element_distribute_name']  ?? null;

                $allows_choose_distribute  =      filter_var($bonus_product['allows_choose_distribute'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $status_stock = null;
                if ($allows_choose_distribute == true) {
                } else {
                    $status_stock =  ProductUtils::get_id_distribute_and_stock(
                        $request->store->id,
                        null,
                        $product_id,
                        $distribute_name,
                        $element_distribute_name,
                        $sub_element_distribute_name
                    );
                }


                $lineItem = BonusProductItem::create(
                    [
                        'store_id' => $request->store->id,
                        'bonus_product_id' =>  $bonusProductCreate->id,
                        'product_id' =>   $product_id,
                        'is_select_product' => false,
                        'allows_choose_distribute' => $allows_choose_distribute,
                        'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                        'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,
                        'quantity' => $bonus_product['quantity'] ?? 1,

                        "distribute_name"  =>  $distribute_name,
                        "element_distribute_name" => $element_distribute_name,
                        "sub_element_distribute_name"   => $sub_element_distribute_name,
                    ]
                );
            }
        } else {
            $product_id = $request->data_ladder['product_id'];
            $element_distribute_name = $request->data_ladder['element_distribute_name'];
            $sub_element_distribute_name = $request->data_ladder['sub_element_distribute_name'];
            $distribute_name = $request->data_ladder['distribute_name'];

            $status_stock =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                null,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );


            foreach ($request->data_ladder['list'] as $itemLadder) {

                $bo_product_id = $itemLadder['bo_product_id'] ?? null;
                $bo_element_distribute_name = $itemLadder['bo_element_distribute_name'] ?? null;
                $bo_sub_element_distribute_name = $itemLadder['bo_sub_element_distribute_name'] ?? null;
                $bo_distribute_name = $itemLadder['bo_distribute_name'] ?? null;

                $status_stock_item =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $bo_product_id,
                    $bo_element_distribute_name,
                    $bo_sub_element_distribute_name,
                    $bo_distribute_name
                );

                $lineItem = BonusProductItemLadder::create(
                    [
                        'store_id' => $request->store->id,
                        'bonus_product_id' =>  $bonusProductCreate->id,
                        'product_id' =>   $product_id,
                        // 'is_select_product' => false,
                        // 'allows_choose_distribute' => $allows_choose_distribute,
                        'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                        'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,

                        "distribute_name"  =>  $distribute_name,
                        "element_distribute_name" => $element_distribute_name,
                        "sub_element_distribute_name"   => $sub_element_distribute_name,

                        'bo_element_distribute_id' =>  $status_stock_item['element_distribute_id']  ?? null,
                        'bo_sub_element_distribute_id' =>  $status_stock_item['sub_element_distribute_id']  ?? null,

                        'bo_product_id' => $bo_product_id,

                        'from_quantity' => $itemLadder['from_quantity'] ?? 1,
                        'bo_quantity' => $itemLadder['bonus_quantity'] ?? 1,

                        "bo_distribute_name"  =>  $bo_distribute_name,
                        "bo_element_distribute_name" => $bo_element_distribute_name,
                        "bo_sub_element_distribute_name"   => $bo_sub_element_distribute_name,
                    ]
                );
            }
        }


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $this->bonus_product_one_data($bonusProductCreate->id),
        ], 201);
    }

    /**
     * Update combo
     * @urlParam  store_code required Store code
     * @urlParam  bonus_product_id required Id bonus_product_id
     * @bodyParam is_end boolean required Chương trình đã kết thúc chưa
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thời gian kết thúc
     * @bodyParam multiply_by_number bool nhan theo so luong
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam select_products List<json> required danh sách sản phẩm mua và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1, quantity: 10} ]
     * @bodyParam bonus_products List<json> required danh sách sản phẩm được tặng và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1,, quantity: 10} ]
     * @bodyParam allows_choose_distribute boolean required thêm cái này vào ds nếu cho phép chọn phân loại sp thưởng
     * @bodyParam multiply_by_number bool nhan theo so luong
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * 
     * 
     */


    public function updateOne(Request $request)
    {

        $id = $request->route()->parameter('bonus_product_id');
        $checkBonusProductExists = BonusProduct::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkBonusProductExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 400);
        }

        ///// Trường hợp kết thúc luôn
        if (filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN) == true) {
            $newData = [
                'is_end' => filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN),
            ];

            $checkBonusProductExists->update(
                $newData
            );
            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'data' => $this->bonus_product_one_data($checkBonusProductExists->id),
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



        $ladder_reward =  filter_var($request->ladder_reward, FILTER_VALIDATE_BOOLEAN);


        if ($ladder_reward  == false) {
        } else {
            BonusProductItemLadder::where('bonus_product_id', $id)->delete();
        }


        //  ProductCombo::whereIn('product_id', $productIds)->delete();
        ///////================================
        $group_types = $checkBonusProductExists->group_types;
        $agency_types = $checkBonusProductExists->agency_types;
        $group_customers = $checkBonusProductExists->group_customers;

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
            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'multiply_by_number' => $request->multiply_by_number,
            'used' => $request->amount != $checkBonusProductExists->amount ? 0 :   $checkBonusProductExists->used,

            "ladder_reward" =>  $ladder_reward,
            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'group_customers' => $group_customers,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ];

        $checkBonusProductExists->update(
            $newData
        );

        if ($ladder_reward  == false) {
        } else {
            $product_id = $request->data_ladder['product_id'];
            $element_distribute_name = $request->data_ladder['element_distribute_name'];
            $sub_element_distribute_name = $request->data_ladder['sub_element_distribute_name'];
            $distribute_name = $request->data_ladder['distribute_name'];

            $status_stock =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                null,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            foreach ($request->data_ladder['list'] as $itemLadder) {

                $bo_product_id = $itemLadder['bo_product_id'] ?? null;
                $bo_element_distribute_name = $itemLadder['bo_element_distribute_name'] ?? null;
                $bo_sub_element_distribute_name = $itemLadder['bo_sub_element_distribute_name'] ?? null;
                $bo_distribute_name = $itemLadder['bo_distribute_name'] ?? null;

                $status_stock_item_bo =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $bo_product_id,
                    $bo_element_distribute_name,
                    $bo_sub_element_distribute_name,
                    $bo_distribute_name
                );

                $lineItem = BonusProductItemLadder::create(
                    [
                        'store_id' => $request->store->id,
                        'bonus_product_id' =>  $checkBonusProductExists->id,
                        'product_id' =>   $product_id,
                        // 'is_select_product' => false,
                        // 'allows_choose_distribute' => $allows_choose_distribute,
                        'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                        'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,

                        "distribute_name"  =>  $distribute_name,
                        "element_distribute_name" => $element_distribute_name,
                        "sub_element_distribute_name"   => $sub_element_distribute_name,

                        'bo_element_distribute_id' =>  $status_stock_item_bo['element_distribute_id']  ?? null,
                        'bo_sub_element_distribute_id' =>  $status_stock_item_bo['sub_element_distribute_id']  ?? null,

                        'bo_product_id' => $bo_product_id,

                        'from_quantity' => $itemLadder['from_quantity'] ?? 1,
                        'bo_quantity' => $itemLadder['bonus_quantity'] ?? 1,

                        "bo_distribute_name"  =>  $bo_distribute_name,
                        "bo_element_distribute_name" => $bo_element_distribute_name,
                        "bo_sub_element_distribute_name"   => $bo_sub_element_distribute_name,
                    ]
                );
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $this->bonus_product_one_data($checkBonusProductExists->id),

        ], 201);
    }

    /**
     * Xem tất cả combo chuẩn vị và đang phát hàng
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request, $id)
    {
        $now = Helper::getTimeNowDateTime();
        $combos = BonusProduct::where('store_id', $request->store->id,)
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

    function bonus_product_one_data($id)
    {
        $bonus_product = BonusProduct::where('id', $id)->first();

        $bonus_group_product = $bonus_product->bonus_product_items()->pluck('group_product')->unique()->toArray();
        // $bonus_product->select_products = BonusProductItem::where('bonus_product_id', $id)->where('is_select_product', true)->get();
        // $bonus_product->bonus_products = BonusProductItem::where('bonus_product_id', $id)->where('is_select_product', false)->get();
        $bonus_product->group_products = array_values($bonus_group_product);
        $bonus_product->bonus_products_ladder = BonusProductItemLadder::where('bonus_product_id', $id)->orderBy('from_quantity', 'asc')->get();

        return  $bonus_product;
    }

    /**
     * Xem 1 Bonus
     * @urlParam  store_code required Store code
     * @urlParam  bonus_product_id required Id bonus_product_id
     */
    public function getOne(Request $request)
    {

        $id = $request->route()->parameter('bonus_product_id');
        $checkBonusProductExists = BonusProduct::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkBonusProductExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $this->bonus_product_one_data($checkBonusProductExists->id),
        ], 200);
    }

    /**
     * Xem tất cả Bonus Product đã kết thúc
     * @queryParam  page Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)
     * @urlParam  store_code required Store code
     */
    public function getAllEnd(Request $request, $id)
    {
        $now = Helper::getTimeNowDateTime();
        $BonusProducts = BonusProduct::where('store_id', $request->store->id)
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
            'data' =>  $BonusProducts,
        ], 200);
    }

    /**
     * xóa một chương trình Bonus Product
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID combo cần xóa thông tin.
     */
    public function deleteOne(Request $request)
    {
        $id = $request->route()->parameter('bonus_product_id');
        $comboExists = BonusProduct::where('id', $id)->first();

        if (empty($comboExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 404);
        }

        $comboExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $comboExists->id],
        ], 200);
    }

    //Xử lý BonusProductItem
    /**
     * Xem 1 Bonus item
     * @urlParam  store_code required Store code
     * @urlParam  bonus_product_id required Id bonus_product_id
     * @urlParam  bonus_product_item_ids required Id bonus_product_item_ids
     * @urlParam  group_product required Int group_product
     */

    public function getOneItem(Request $request)
    {
        $id = request('bonus_product_id');
        $group_product = (int)request('group_product');

        $bonus_product_exists = BonusProduct::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($bonus_product_exists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 400);
        }

        if (request('group_product') === null || request('group_product') === "") {
            $bonus_product_item_first = $bonus_product_exists->bonus_product_items()->first();

            if ($bonus_product_item_first) {
                $group_product = $bonus_product_item_first->group_product;
            }
        }

        $group_product_max = $bonus_product_exists->bonus_product_items()->max('group_product');
        $data = [
            'group_product_current' => $group_product,
            'group_product_max' => $group_product_max,
            'select_products' => $bonus_product_exists->select_products()
                ->where('group_product', $group_product)
                ->get(),
            'bonus_products' => $bonus_product_exists->bonus_products()
                ->where('group_product', $group_product)
                ->get(),
        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }

    /**
     * Cập nhập nhóm sản phẩm thưởng
     * @urlParam   store_code required Store code
     * @urlParam   bonus_product_id required Id bonus_product_id
     * @bodyParam  allows_choose_distribute boolean required thêm cái này vào ds nếu cho phép chọn phân loại sp thưởng
     * @bodyParam select_products List<json> required danh sách sản phẩm mua và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1, quantity: 10} ]
     * @bodyParam bonus_products List<json> required danh sách sản phẩm được tặng và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1,, quantity: 10} ]
     * @bodyParam  group_product required Int Nhóm sản phẩm thưởng
     */

    public function createItem(Request $request)
    {
        $id = $request->route()->parameter('bonus_product_id');
        $checkBonusProductExists = BonusProduct::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkBonusProductExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 400);
        }

        if (!$checkBonusProductExists->start_time || !$checkBonusProductExists->end_time) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 400);
        }

        $start_time = Carbon::createFromFormat('Y-m-d H:i:s', $checkBonusProductExists->start_time);
        $end_time = Carbon::createFromFormat('Y-m-d H:i:s', $checkBonusProductExists->end_time);

        if ($checkBonusProductExists->is_end == true && $end_time->gt($start_time)) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::PRODUCT_BONUS_END_EXISTS[0],
                'msg' => MsgCode::PRODUCT_BONUS_END_EXISTS[1],
            ], 400);
        }

        $group_product_max = $checkBonusProductExists->bonus_product_items()->max('group_product');
        $group_product_new = $group_product_max + 1;
        $select_products = $request->select_products;

        if ($select_products  == null || count($select_products) == 0) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thêm sản phẩm thưởng",
            ], 404);
        }

        //Check product select
        foreach ($select_products  as $select_product) {
            $product_id = $select_product['product_id'];

            $distribute_name = $select_product['distribute_name'] ?? null;
            $element_distribute_name = $select_product['element_distribute_name'] ?? null;
            $sub_element_distribute_name = $select_product['sub_element_distribute_name'] ?? null;
            $allows_all_distribute = $select_product['allows_all_distribute'] ?? null;

            $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
                ->first();

            if ($productExist == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                ], 404);
            }

            if ($allows_all_distribute == true) {

                $bonus_product_ids = BonusProductItem::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'product_id',
                    $product_id
                )->where('allows_all_distribute', true)
                    ->where(
                        'is_select_product',
                        true
                    )
                    ->get()->pluck('bonus_product_id');
            } else {
                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                if ($status_stock == null || ProductUtils::check_type_distribute($productExist) != $status_stock['type']) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::VERSION_NOT_FOUND_PRODUCT[0],
                        'msg' => MsgCode::VERSION_NOT_FOUND_PRODUCT[1],
                    ], 400);
                }

                $bonus_product_ids = BonusProductItem::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'product_id',
                    $product_id
                )
                    ->where(
                        'is_select_product',
                        true
                    )->where(
                        'element_distribute_id',
                        $status_stock['element_distribute_id']  ?? null
                    )->where(
                        'sub_element_distribute_id',
                        $status_stock['sub_element_distribute_id']  ?? null
                    )
                    ->get()->pluck('bonus_product_id');
            }
        }

        $bonus_products = $request->bonus_products;

        if ($bonus_products  == null || count($bonus_products) == 0) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thêm sản phẩm thưởng",
            ], 404);
        }
        //Check product bonus
        foreach ($bonus_products  as $bonus_product) {
            $product_id = $bonus_product['product_id'];

            $distribute_name = $bonus_product['distribute_name'] ?? null;
            $element_distribute_name = $bonus_product['element_distribute_name'] ?? null;
            $sub_element_distribute_name = $bonus_product['sub_element_distribute_name'] ?? null;

            $allows_choose_distribute  = $bonus_product['allows_choose_distribute'] ?? false;

            $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
                ->first();

            if ($productExist == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                ], 404);
            }

            if ($allows_choose_distribute == true) {
            } else {

                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                if ($status_stock == null || ProductUtils::check_type_distribute($productExist) != $status_stock['type']) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::VERSION_NOT_FOUND_BONUS_PRODUCT[0],
                        'msg' => MsgCode::VERSION_NOT_FOUND_BONUS_PRODUCT[1],
                    ], 400);
                }
            }
        }
        //Add item line select
        foreach ($select_products as $select_product) {
            $product_id = $select_product['product_id'];

            $distribute_name = $select_product['distribute_name']  ?? null;
            $element_distribute_name = $select_product['element_distribute_name']  ?? null;
            $sub_element_distribute_name = $select_product['sub_element_distribute_name']  ?? null;
            $allows_all_distribute = $select_product['allows_all_distribute']  ?? null;

            $status_stock =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                null,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            BonusProductItem::create(
                [
                    'store_id' => $request->store->id,
                    'bonus_product_id' =>  $checkBonusProductExists->id,
                    'product_id' =>   $product_id,
                    'is_select_product' => true,
                    'group_product' => $group_product_new,
                    'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,
                    "distribute_name"  =>  $distribute_name,
                    'element_distribute_name' => $element_distribute_name,
                    'sub_element_distribute_name' => $sub_element_distribute_name,
                    'quantity' => $select_product['quantity'] ?? 1,
                    'allows_all_distribute' =>   $allows_all_distribute
                ]
            );
        }

        //Add item line bonus
        foreach ($bonus_products as $bonus_product) {
            $product_id = $bonus_product['product_id'];

            $distribute_name = $bonus_product['distribute_name']  ?? null;
            $element_distribute_name = $bonus_product['element_distribute_name']  ?? null;
            $sub_element_distribute_name = $bonus_product['sub_element_distribute_name']  ?? null;

            $allows_choose_distribute  =      filter_var($bonus_product['allows_choose_distribute'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $status_stock = null;
            if ($allows_choose_distribute == true) {
            } else {
                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );
            }

            BonusProductItem::create(
                [
                    'store_id' => $request->store->id,
                    'bonus_product_id' =>  $checkBonusProductExists->id,
                    'product_id' =>   $product_id,
                    'is_select_product' => false,
                    'group_product' => $group_product_new,
                    'allows_choose_distribute' => $allows_choose_distribute,
                    'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,
                    'quantity' => $bonus_product['quantity'] ?? 1,

                    "distribute_name"  =>  $distribute_name,
                    "element_distribute_name" => $element_distribute_name,
                    "sub_element_distribute_name"   => $sub_element_distribute_name,
                ]
            );
        }

        $request->merge([
            "group_product" => $group_product_new,
            "bonus_product_id" => $checkBonusProductExists->id,
        ]);

        return $this->getOneItem($request);
    }

    /**
     * Cập nhập nhóm sản phẩm thưởng
     * @urlParam   store_code required Store code
     * @urlParam   bonus_product_id required Id bonus_product_id
     * @bodyParam  allows_choose_distribute boolean required thêm cái này vào ds nếu cho phép chọn phân loại sp thưởng
     * @bodyParam select_products List<json> required danh sách sản phẩm mua và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1, quantity: 10} ]
     * @bodyParam bonus_products List<json> required danh sách sản phẩm được tặng và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1,, quantity: 10} ]
     * @bodyParam  group_product required Int Nhóm sản phẩm thưởng
     */

    public function updateOneItem(Request $request)
    {
        $id = $request->route()->parameter('bonus_product_id');
        $group_product = (int)$request->group_product;

        if ($request->group_product === null || $request->group_product === '') {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_GROUP_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_GROUP_PRODUCT_EXISTS[1],
            ], 400);
        }

        $checkBonusProductExists = BonusProduct::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkBonusProductExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 400);
        }

        if (!$checkBonusProductExists->start_time || !$checkBonusProductExists->end_time) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 400);
        }

        $start_time = Carbon::createFromFormat('Y-m-d H:i:s', $checkBonusProductExists->start_time);
        $end_time = Carbon::createFromFormat('Y-m-d H:i:s', $checkBonusProductExists->end_time);

        if ($checkBonusProductExists->is_end == true && $end_time->gt($start_time)) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::PRODUCT_BONUS_END_EXISTS[0],
                'msg' => MsgCode::PRODUCT_BONUS_END_EXISTS[1],
            ], 400);
        }

        $select_products = $request->select_products;

        if ($select_products  == null || count($select_products) == 0) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thêm sản phẩm thưởng",
            ], 404);
        }

        //Check product select
        foreach ($select_products  as $select_product) {
            $product_id = $select_product['product_id'];

            $distribute_name = $select_product['distribute_name']  ?? null;
            $element_distribute_name = $select_product['element_distribute_name']  ?? null;
            $sub_element_distribute_name = $select_product['sub_element_distribute_name']  ?? null;

            $allows_all_distribute = $select_product['allows_all_distribute'] ?? null;

            $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
                ->first();

            if ($productExist == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                ], 404);
            }

            if ($allows_all_distribute == true) {
                $bonus_product_ids = BonusProductItem::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'product_id',
                    $product_id
                )->where('allows_all_distribute', true)
                    ->where(
                        'is_select_product',
                        true
                    )
                    ->get()->pluck('bonus_product_id');
            } else {

                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                if ($status_stock == null || ProductUtils::check_type_distribute($productExist) != $status_stock['type']) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::VERSION_NOT_FOUND_PRODUCT[0],
                        'msg' => MsgCode::VERSION_NOT_FOUND_PRODUCT[1],
                    ], 400);
                }


                $bonus_product_ids = BonusProductItem::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'product_id',
                    $product_id
                )->where('bonus_product_id', '!=',   $id)
                    ->where(
                        'is_select_product',
                        true
                    )->where(
                        'element_distribute_id',
                        $status_stock['element_distribute_id']  ?? null
                    )->where(
                        'sub_element_distribute_id',
                        $status_stock['sub_element_distribute_id']  ?? null
                    )
                    ->get()->pluck('bonus_product_id');
            }
        }

        $bonus_products = $request->bonus_products;

        if ($bonus_products  == null || count($bonus_products) == 0) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Thêm sản phẩm thưởng",
            ], 404);
        }
        //Check product select
        foreach ($bonus_products  as $bonus_product) {
            $product_id = $bonus_product['product_id'];

            $distribute_name = $bonus_product['distribute_name'] ?? null;
            $element_distribute_name = $bonus_product['element_distribute_name'] ?? null;
            $sub_element_distribute_name = $bonus_product['sub_element_distribute_name'] ?? null;

            $allows_choose_distribute  =      filter_var($bonus_product['allows_choose_distribute'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $productExist = Product::where('id', $product_id)->where('store_id', $request->store->id)
                ->first();

            if ($productExist == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
                ], 404);
            }

            if ($allows_choose_distribute  == true) {
            } else {
                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );

                if ($status_stock == null || ProductUtils::check_type_distribute($productExist) != $status_stock['type']) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::VERSION_NOT_FOUND_BONUS_PRODUCT[0],
                        'msg' => MsgCode::VERSION_NOT_FOUND_BONUS_PRODUCT[1],
                    ], 400);
                }
            }
        }

        $checkBonusProductExists->bonus_product_items()->where('group_product', $group_product)
            ->delete();

        //Add item line select
        foreach ($select_products as $select_product) {
            $product_id = $select_product['product_id'];

            $distribute_name = $select_product['distribute_name']  ?? null;
            $element_distribute_name = $select_product['element_distribute_name']  ?? null;
            $sub_element_distribute_name = $select_product['sub_element_distribute_name']  ?? null;
            $allows_all_distribute = $select_product['allows_all_distribute']  ?? null;

            $status_stock =  ProductUtils::get_id_distribute_and_stock(
                $request->store->id,
                null,
                $product_id,
                $distribute_name,
                $element_distribute_name,
                $sub_element_distribute_name
            );

            $lineItem = BonusProductItem::create(
                [
                    'store_id' => $request->store->id,
                    'bonus_product_id' =>  $checkBonusProductExists->id,
                    'product_id' =>   $product_id,
                    'is_select_product' => true,
                    'group_product' => $group_product,
                    'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,
                    'quantity' => $select_product['quantity'] ?? 1,
                    "distribute_name"  =>  $distribute_name,
                    'element_distribute_name' => $element_distribute_name,
                    'sub_element_distribute_name' => $sub_element_distribute_name,
                    'allows_all_distribute' => $allows_all_distribute
                ]
            );
        }

        //Add item line bonus
        foreach ($bonus_products as $bonus_product) {
            $product_id = $bonus_product['product_id'];

            $distribute_name = $bonus_product['distribute_name'] ?? null;
            $element_distribute_name = $bonus_product['element_distribute_name'] ?? null;
            $sub_element_distribute_name = $bonus_product['sub_element_distribute_name'] ?? null;
            $allows_choose_distribute  =      filter_var($bonus_product['allows_choose_distribute'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $status_stock  = null;
            if ($allows_choose_distribute  == true) {
            } else {
                $status_stock =  ProductUtils::get_id_distribute_and_stock(
                    $request->store->id,
                    null,
                    $product_id,
                    $distribute_name,
                    $element_distribute_name,
                    $sub_element_distribute_name
                );
            }


            $lineItem = BonusProductItem::create(
                [
                    'store_id' => $request->store->id,
                    'bonus_product_id' =>  $checkBonusProductExists->id,
                    'product_id' =>   $product_id,
                    'is_select_product' => false,
                    'group_product' => $group_product,
                    'allows_choose_distribute' => $allows_choose_distribute,
                    'element_distribute_id' =>  $status_stock['element_distribute_id']  ?? null,
                    'sub_element_distribute_id' =>  $status_stock['sub_element_distribute_id']  ?? null,
                    'quantity' => $bonus_product['quantity'] ?? 1,

                    "distribute_name"  =>  $distribute_name,
                    "element_distribute_name" => $element_distribute_name,
                    "sub_element_distribute_name"   => $sub_element_distribute_name,
                ]
            );
        }

        $request->merge([
            "group_product" => $group_product,
            "bonus_product_id" => $checkBonusProductExists->id,
        ]);

        return $this->getOneItem($request);
    }

    public function destroyOneItem(Request $request)
    {
        $id = $request->route()->parameter('bonus_product_id');
        $group_product = (int)$request->group_product;

        if ($request->group_product === null || $request->group_product === '') {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_GROUP_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_GROUP_PRODUCT_EXISTS[1],
            ], 400);
        }

        $checkBonusProductExists = BonusProduct::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkBonusProductExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_BONUS_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_BONUS_EXISTS[1],
            ], 400);
        }

        if (!$checkBonusProductExists->start_time || !$checkBonusProductExists->end_time) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 400);
        }

        $start_time = Carbon::createFromFormat('Y-m-d H:i:s', $checkBonusProductExists->start_time);
        $end_time = Carbon::createFromFormat('Y-m-d H:i:s', $checkBonusProductExists->end_time);

        if ($checkBonusProductExists->is_end == true && $end_time->gt($start_time)) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::PRODUCT_BONUS_END_EXISTS[0],
                'msg' => MsgCode::PRODUCT_BONUS_END_EXISTS[1],
            ], 400);
        }

        $checkBonusProductExists->bonus_product_items()->where('group_product', $group_product)
            ->delete();

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::DELETE_GROUP_PRODUCT_EXISTS[0],
            'msg' => MsgCode::DELETE_GROUP_PRODUCT_EXISTS[1],
        ], 201);
    }
}
