<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductDiscount;
use DateTime;
use Illuminate\Http\Request;

/**
 * @group  User/Giảm giá sản phẩm
 */

class DiscountController extends Controller
{

    /**
     * Tạo giảm giá sản phẩm
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
     * @bodyParam value float required Giá trị % giảm giá 1 - 99
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam product_ids List<int> required danh sách id sản phẩm kèm số lượng 1,2,...
     * @bodyParam group_customer int required 0 tất cả, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng, 5 khách lẻ
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2 
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * @bodyParam group_customers array required danh sách id của nhóm áp dụng VD: [0,1,2]
     * @bodyParam group_types array required VD: group_types => [{id: 1, name: Sỉ lẻ}]
     * @bodyParam agency_types array required VD: agency_types => [{id: 1, name: Cấp 1}]
     */

    public function create(Request $request)
    {
        $productIds    = request("product_ids") == null ? [] : explode(',', request("product_ids"));

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


        if (count($productIds) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        //Check valid list id product

        foreach ($productIds as $product_id) {

            if (!isset($product_id)) {
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
        }


        //check exist promotion
        foreach ($productIds as $product_id) {

            $product_discount_ids = ProductDiscount::where(
                'store_id',
                $request->store->id
            )->where(
                'product_id',
                $product_id
            )->get()->pluck('discount_id');


            if (count($product_discount_ids)  == 0) break;

            $discounts = Discount::whereIn('id', $product_discount_ids)->get();
            if (count($discounts)  == 0) break;

            // foreach ($discounts  as $discountItem) {
            //     if ($discountItem != null && $discountItem->comingOrHappenning() == true) {
            //         return response()->json([
            //             'code' => 400,
            //             'success' => false,
            //             'msg_code' => MsgCode::PRODUCT_EXIS_IN_DISCOUNT[0] . "|" . $product_id,
            //             'msg' => MsgCode::PRODUCT_EXIS_IN_DISCOUNT[1],
            //         ], 400);
            //     }
            // }
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

        $data  = [
            'is_end' => false,
            'store_id' => $request->store->id,
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'value' => $request->value,
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

        if (count($data) == 2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => $data[0],
                'msg' => $data[1],
            ], 400);
        }



        $discountCreate = Discount::create(
            $data
        );


        //Add item line
        foreach ($productIds as $product_id) {
            $lineItem = ProductDiscount::create(
                [
                    'store_id' => $request->store->id,
                    'discount_id' =>  $discountCreate->id,
                    'product_id' => $product_id,
                ]
            );
        }



        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Thêm chương trình giảm giá " . $discountCreate->name,
            $discountCreate->id,
            $discountCreate->name
        );

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Discount::where('id', $discountCreate->id)
                ->first()
        ], 201);
    }

    /**
     * Update giảm giá sản phẩm
     * Muốn kết thúc chương trình chỉ cần truyền is_end = false (Còn lại truyền đầy đủ)
     * @urlParam  store_code required Store code
     * @urlParam  discount_id required Id discount
     * @bodyParam is_end boolean required Chương trình đã kết thúc chưa
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
     * @bodyParam value float required Giá trị % giảm giá 1 - 99
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam product_ids List<int> required danh sách id sản phẩm kèm số lượng 1,2,...
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * 
     */


    public function updateOneDiscount(Request $request)
    {
        $id = $request->route()->parameter('discount_id');
        $checkDiscountExists = Discount::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkDiscountExists == null) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_DISCOUNT_EXISTS[0],
                'msg' => MsgCode::NO_DISCOUNT_EXISTS[1],
            ], 400);
        }

        ///// Trường hợp kết thúc luôn
        if (filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN) == true) {
            $newData = [
                'is_end' => filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN),
            ];

            $checkDiscountExists->update(
                $newData
            );
            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => Discount::where('id', $checkDiscountExists->id)
                    ->first()
            ], 201);
        }

        $productIds    = request("product_ids") == null ? [] : explode(',', request("product_ids"));

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

        if (count($productIds) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        //Check valid list id product

        foreach ($productIds as $product_id) {

            if (!isset($product_id)) {
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
        }


        $arrProductInDiscountExits = $checkDiscountExists->product_discount()->get()->pluck('product_id')->toArray();

        //check exist promotion
        foreach ($productIds as $product_id) {

            if (!in_array($product_id, $arrProductInDiscountExits)) {

                $product_discount = ProductDiscount::where(
                    'store_id',
                    $request->store->id
                )->where(
                    'product_id',
                    $product_id
                )->get()->pluck('discount_id');


                if (count($product_discount)  == 0) break;

                $discounts = Discount::whereIn('id', $product_discount)->get();
                if (count($discounts)  == 0) break;

                // foreach ($discounts  as $discountItem) {
                //     if ($discountItem != null && $discountItem->comingOrHappenning() == true) {

                //         return response()->json([
                //             'code' => 400,
                //             'success' => false,
                //             'msg_code' => MsgCode::PRODUCT_EXIS_IN_DISCOUNT[0] . "|" . $product_id,
                //             'msg' => MsgCode::PRODUCT_EXIS_IN_DISCOUNT[1],
                //         ], 400);
                //     }
                // }
            }
        }

        ///////Remove 
        ProductDiscount::where('discount_id', $id)->delete();

        //  ProductDiscount::whereIn('product_id', $productIds)->delete();
        ///////================================
        $group_types = $checkDiscountExists->group_types;
        $agency_types = $checkDiscountExists->agency_types;
        $group_customers = $checkDiscountExists->group_customers;

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
            'value' => $request->value,
            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'used' => $request->amount != $checkDiscountExists->amount   ? 0 :   $checkDiscountExists->used,

            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'group_customers' => $group_customers,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ];

        $checkDiscountExists->update(
            $newData
        );


        //Add item line
        foreach ($productIds as $product_id) {
            $lineItem = ProductDiscount::create(
                [
                    'store_id' => $request->store->id,
                    'discount_id' =>    $checkDiscountExists->id,
                    'product_id' => $product_id,
                ]
            );
        }


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Cập nhật chương trình giảm giá " . $checkDiscountExists->name,
            $checkDiscountExists->id,
            $checkDiscountExists->name
        );

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Discount::where('id', $checkDiscountExists->id)
                ->first()
        ], 201);
    }

    /**
     * Xem 1 chương trình giảm giá
     * @urlParam  store_code required Store code
     * @urlParam  discount_id required Id discount
     */
    public function getOneDiscount(Request $request)
    {

        $id = $request->route()->parameter('discount_id');
        $checkDiscountExists = Discount::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkDiscountExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_DISCOUNT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkDiscountExists,
        ], 200);
    }

    /**
     * Xem tất cả chương trình giảm giá chuẩn vị và đang phát hàng
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request)
    {

        $now = Helper::getTimeNowDateTime();
        $discounts = Discount::where('store_id', $request->store->id,)

            ->where('is_end', '=', false)
            ->where('end_time', '>=', $now)
            ->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $discounts,
        ], 200);
    }


    /**
     * Xem tất cả Discount đã kết thúc
     * @queryParam  page Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)
     * @urlParam  store_code required Store code
     */
    public function getAllEnd(Request $request)
    {

        $now = Helper::getTimeNowDateTime();
        $discounts = Discount::where('store_id', $request->store->id,)
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
            'data' => $discounts,
        ], 200);
    }



    /**
     * xóa một chương trình discount
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID discount cần xóa thông tin.
     */
    public function deleteOneDiscount(Request $request)
    {
        $id = $request->route()->parameter('discount_id');
        $discountExists = Discount::where('id', $id)->first();

        if (empty($discountExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DISCOUNT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Xóa chương trình giảm giá " . $discountExists->name,
            $discountExists->id,
            $discountExists->name
        );

        $discountExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $discountExists->id],
        ], 200);
    }
}
