<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\StringUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ProductVoucher;
use App\Models\VoucherCode;
use DateTime;
use Illuminate\Http\Request;

/**
 * @group  User/Voucher
 * discount_type // 0 gia co dinh - 1 theo phan tram
 * voucher_type //0 All - 1 Mot So sp
 * set_limit_total khi set giá trị true - yêu cầu khách hàng phải mua đủ sản phẩm
 * thuộc voucher_type 1
 */

class VoucherController extends Controller
{

    /**
     * Tạo voucher mới
     * @urlParam  store_code required Store code
     * @bodyParam name string required Tên chương trình
     * @bodyParam is_show_voucher boolean required Có hiển thị cho khách hàng thấy không
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
     
     * @bodyParam discount_for int required 0 trừ vào đơn hàng,1 trừ phí ship
     * @bodyParam is_free_ship bool required dành cho discount_for == 1
     * @bodyParam ship_discount_value double required dành cho discount_for == 1

     * @bodyParam voucher_type int required 0 áp dụng tất cả sp - 1 cho một số sản phẩm
     * @bodyParam discount_type int required (voucher_type == 1) 0 giám giá cố định - 1 theo %
     * @bodyParam value_discount float required (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
     * @bodyParam set_limit_value_discount boolean required (value_discount ==1) set giá trị giảm tối đa hay không
     * @bodyParam max_value_discount float required giá trị giảm tối đa 
     * @bodyParam set_limit_total boolean required Có tối thiểu hóa đơn hay không
     * @bodyParam value_limit_total float required Giá trị tối thiểu của hóa đơn

     * @bodyParam value float required Giá trị % giảm giá 1 - 99
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam product_ids List<int> required danh sách id sản phẩm kèm số lượng 1,2,...
     * 
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * @bodyParam is_public boolean required Có hiển thị cho khách hàng thấy không
     * @bodyParam is_use_once boolean required Mỗi khách chỉ được sử dụng một hay nhiều lần voucher
     * @bodyParam group_customers array required danh sách id của nhóm áp dụng VD: [0,1,2]
     * @bodyParam group_types array required VD: group_types => [{id: 1, name: Sỉ lẻ}]
     * @bodyParam agency_types array required VD: agency_types => [{id: 1, name: Cấp 1}]
     * 
     */

    public function create(Request $request)
    {
        $productIds    = request("product_ids") == null ? [] : explode(',', request("product_ids"));
        $amount_use_once = (int)$request->amount_use_once;
        $voucher_length = (int)$request->voucher_length;
        $starting_character = $request->starting_character;

        if ($request->code == null && ($request->is_use_once_code_multiple_time || $request->is_use_once_code_multiple_time === null)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CODE_IS_REQUIRED[0],
                'msg' => MsgCode::CODE_IS_REQUIRED[1],
            ], 400);
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

        if ($request->discount_for != 1) {


            if ($request->discount_type != 1 && $request->discount_type != 0) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_VOUCHER_DISCOUNT_TYPE[0],
                    'msg' => MsgCode::INVALID_VOUCHER_DISCOUNT_TYPE[1],
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
        }

        if ($request->is_use_once_code_multiple_time === false) {
            if ($amount_use_once === 0) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::AMOUNT_VOUCHER_CAN_USE_REQUIRED[0],
                    'msg' => MsgCode::AMOUNT_VOUCHER_CAN_USE_REQUIRED[1],
                ], 400);
            }

            if ($voucher_length === 0) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::VOUCHER_CODE_LENGTH_REQUIRED[0],
                    'msg' => MsgCode::VOUCHER_CODE_LENGTH_REQUIRED[1],
                ], 400);
            }

            if ($starting_character === null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::STARTING_CHARACTER__REQUIRED[0],
                    'msg' => MsgCode::STARTING_CHARACTER__REQUIRED[1],
                ], 400);
            }

            if ($voucher_length <= strlen($starting_character)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::VOUCHER_CODE_LENGTH_INVALID[0],
                    'msg' => MsgCode::VOUCHER_CODE_LENGTH_INVALID[1],
                ], 400);
            }

            $hasExistVoucher = Voucher::where('store_id', $request->store->id)
                ->whereRaw("LENGTH(`code`) = ?", [$voucher_length])
                ->whereRaw("LEFT(`code`, " . strlen($starting_character) . ") = ?", [strtoupper($starting_character)])
                ->exists();

            if ($hasExistVoucher) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::STARTING_CHARACTER__EXISTS[0],
                    'msg' => MsgCode::STARTING_CHARACTER__EXISTS[1],
                ], 400);
            }

            $hasExitVoucherCode = VoucherCode::where('store_id', $request->store->id)
                ->whereRaw("LENGTH(`code`) = ?", [$voucher_length])
                ->whereRaw("LEFT(`code`, " . strlen($starting_character) . ") = ?", [strtoupper($starting_character)])
                ->exists();

            if ($hasExitVoucherCode) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::STARTING_CHARACTER__EXISTS[0],
                    'msg' => MsgCode::STARTING_CHARACTER__EXISTS[1],
                ], 400);
            }

            $countSortLetters = Helper::countSortLetters($voucher_length - strlen($starting_character));

            if ($countSortLetters < $amount_use_once) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NUMBER_VOUCHER_CODE_CAN_GENERATE_NOT_ENOUGH[0],
                    'msg' => MsgCode::NUMBER_VOUCHER_CODE_CAN_GENERATE_NOT_ENOUGH[1],
                ], 400);
            }
        }

        $amount = null;
        $setLimit = false;
        if ($request->amount > 0 && filter_var($request->set_limit_amount, FILTER_VALIDATE_BOOLEAN) == true) {
            $amount = $request->amount;
            $setLimit = true;
        }


        $maxValueDiscount = null;
        $setLimitValueDiscount = false;
        if (filter_var($request->set_limit_value_discount, FILTER_VALIDATE_BOOLEAN) == true) {
            $maxValueDiscount = $request->max_value_discount;
            $setLimitValueDiscount = true;
        }

        $valueLimitTotal = null;
        $setLimitTotal = false;
        if (filter_var($request->set_limit_total, FILTER_VALIDATE_BOOLEAN) == true) {
            $valueLimitTotal = $request->value_limit_total;
            $setLimitTotal = true;
        }


        //check exist code
        if ($request->is_use_once_code_multiple_time || $request->is_use_once_code_multiple_time === null) {
            $checkExistsCode = Voucher::where(
                'store_id',
                $request->store->id
            )->where(
                'code',
                strtoupper($request->code)
            )->first();

            if ($checkExistsCode != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::CODE_VOUCHER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::CODE_VOUCHER_ALREADY_EXISTS[1],
                ], 400);
            }

            $checkExistsVoucherCode = VoucherCode::where(
                'store_id',
                $request->store->id
            )->where(
                'code',
                strtoupper($request->code)
            )->exists();

            if ($checkExistsVoucherCode) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::CODE_VOUCHER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::CODE_VOUCHER_ALREADY_EXISTS[1],
                ], 400);
            }
        }


        //Nếu là voucher theo nhóm sản phẩm
        if ($request->voucher_type == 1) {

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
            'voucher_type' => $request->voucher_type == 1 ? 1 : 0,
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,

            'discount_for' => $request->discount_for,
            'is_free_ship' => $request->is_free_ship,
            'ship_discount_value' => $request->ship_discount_value,

            'is_show_voucher' => filter_var($request->is_show_voucher, FILTER_VALIDATE_BOOLEAN),
            'discount_type' => $request->discount_type,
            'value_discount' => $request->value_discount,
            'set_limit_value_discount' => $setLimitValueDiscount,
            'max_value_discount' => $maxValueDiscount,

            'set_limit_total' => $setLimitTotal,
            'value_limit_total' => $valueLimitTotal,

            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'used' => 0,

            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'is_public' => filter_var($request->is_public, FILTER_VALIDATE_BOOLEAN),
            'is_use_once' => filter_var($request->is_use_once, FILTER_VALIDATE_BOOLEAN),
            'is_use_once_code_multiple_time' => $request->is_use_once_code_multiple_time || $request->is_use_once_code_multiple_time === null ? true : false,
            'amount_use_once' =>  $amount_use_once,

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


        $VoucherCreate = Voucher::create(
            $data
        );

        if ($request->voucher_type == 1) {
            //Add item line
            foreach ($productIds as $product_id) {
                $lineItem = ProductVoucher::create(
                    [
                        'store_id' => $request->store->id,
                        'voucher_id' =>  $VoucherCreate->id,
                        'product_id' => $product_id,
                    ]
                );
            }
        }

        if ($request->is_use_once_code_multiple_time === false) {
            $listLettersRandom = Helper::listLettersRandom($voucher_length - strlen($starting_character), $amount_use_once);
            $arrVoucherCode = [];
            foreach ($listLettersRandom as $code) {
                array_push($arrVoucherCode, [
                    'store_id' => $request->store->id,
                    'voucher_id' => $VoucherCreate->id,
                    'code' => strtoupper($starting_character . $code),
                    'status' => 0,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'created_at' => Helper::getTimeNowDateTime(),
                    'updated_at' => Helper::getTimeNowDateTime(),
                ]);
            }

            VoucherCode::insert($arrVoucherCode);
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_ADD,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Tạo voucher " . $VoucherCreate->name,
            $VoucherCreate->id,
            $VoucherCreate->name
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Voucher::where('id', $VoucherCreate->id)
                ->first()
        ], 201);
    }

    /**
     * Update voucher 
     * Muốn kết thúc chương trình chỉ cần truyền is_end = false (Còn lại truyền đầy đủ)
     * @urlParam  store_code required Store code
     * @urlParam  Voucher_id required Id Voucher
     * @bodyParam is_end boolean required Chương trình đã kết thúc chưa
     * @bodyParam is_show_voucher boolean required Có hiển thị cho khách hàng thấy không
     * @bodyParam name string required Tên chương trình
     * @bodyParam description string required Mô tả chương trình
     * @bodyParam image_url string required Link ảnh chương trình
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
  
     * @bodyParam voucher_type int required 0 áp dụng tất cả sp - 1 cho một số sản phẩm
     * @bodyParam discount_type int required (voucher_type == 1) 0 giám giá cố định - 1 theo %
     * @bodyParam value_discount float required (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
     * @bodyParam set_limit_value_discount boolean required (value_discount ==1) set giá trị giảm tối đa hay không
     * @bodyParam max_value_discount float required giá trị giảm tối đa 
     * @bodyParam set_limit_total boolean required Có tối thiểu hóa đơn hay không
     * @bodyParam value_limit_total float required Giá trị tối thiểu của hóa đơn

     * @bodyParam value float required Giá trị % giảm giá 1 - 99
     * @bodyParam set_limit_amount boolean required Set giới hạn khuyến mãi
     * @bodyParam amount int required Giới hạn số lần khuyến mãi có thể sử dụng
     * @bodyParam product_ids List<int> required danh sách id sản phẩm kèm số lượng 1,2,...
     * 
     * * @bodyParam group_customer int required 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
     * @bodyParam group_type_id int required id của group cần xử lý 
     * @bodyParam group_type_name int required name của group cần xử lý 
     * @bodyParam agency_type_id int required id tầng đại lý trường hợp group là 2
     * @bodyParam agency_type_name Tên required name cấp đại lý VD:Cấp 1
     * @bodyParam is_public boolean required Có hiển thị cho khách hàng thấy không
     * @bodyParam is_use_once boolean required Mỗi khách chỉ được sử dụng một hay nhiều lần voucher
     */


    public function updateOneVoucher(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $checkVoucherExists = Voucher::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkVoucherExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 400);
        }

        ///// Trường hợp kết thúc luôn
        if (filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN) == true) {
            $newData = [
                'is_end' => filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN),
            ];

            $checkVoucherExists->update(
                $newData
            );
            return response()->json([
                'code' => 201,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => Voucher::where('id', $checkVoucherExists->id)
                    ->first()
            ], 201);
        }

        $productIds    = request("product_ids") == null ? [] : explode(',', request("product_ids"));

        $err = null;

        if ($checkVoucherExists->is_use_once_code_multiple_time) {

            //check exist code
            $checkExistsCode = Voucher::where(
                'store_id',
                $request->store->id
            )->where(
                'code',
                strtoupper($request->code)
            )->first();

            if ($checkExistsCode != null && $checkVoucherExists->code !=  $checkExistsCode->code) {
                $err =  MsgCode::CODE_VOUCHER_ALREADY_EXISTS;
            }
            if ($request->code == null) {
                $err = MsgCode::CODE_IS_REQUIRED;
            }
        }


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
        if ($request->discount_for != 1) {

            if ($request->discount_type != 1 && $request->discount_type != 0) {
                $err = MsgCode::INVALID_VOUCHER_DISCOUNT_TYPE;
            }

            if ($request->value_discount == null) {
                $err = MsgCode::VALUE_IS_REQUIRED;
            }
        }

        $maxValueDiscount = null;
        $setLimitValueDiscount = false;
        if (filter_var($request->set_limit_value_discount, FILTER_VALIDATE_BOOLEAN) == true) {
            $maxValueDiscount = $request->max_value_discount;
            $setLimitValueDiscount = true;
        }

        $valueLimitTotal = null;
        $setLimitTotal = false;
        if (filter_var($request->set_limit_total, FILTER_VALIDATE_BOOLEAN) == true) {
            $valueLimitTotal = $request->value_limit_total;
            $setLimitTotal = true;
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



        //Nếu là voucher theo nhóm sản phẩm
        if ($request->voucher_type == 1) {

            if (count($productIds) == 0) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                    'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
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
                        'msg' => MsgCode::INVALID_PRODUCT_ITEM[1],
                    ], 400);
                }
            }
        } else {
            $productIds = [];
        }


        ///////Remove 
        ProductVoucher::where('voucher_id', $id)->delete();

        //  ProductVoucher::whereIn('product_id', $productIds)->delete();
        ///////================================

        $group_types = $checkVoucherExists->group_types;
        $agency_types = $checkVoucherExists->agency_types;
        $group_customers = $checkVoucherExists->group_customers;

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
            'voucher_type' => $request->voucher_type == 1 ? 1 : 0,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'image_url' => $request->image_url,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_show_voucher' => filter_var($request->is_show_voucher, FILTER_VALIDATE_BOOLEAN),
            'discount_type' => $request->discount_type,
            'value_discount' => $request->value_discount,
            'set_limit_value_discount' => $setLimitValueDiscount,
            'max_value_discount' => $maxValueDiscount,

            'discount_for' => $request->discount_for,
            'is_free_ship' => $request->is_free_ship,
            'ship_discount_value' => $request->ship_discount_value,

            'set_limit_total' => $setLimitTotal,
            'value_limit_total' => $valueLimitTotal,

            'set_limit_amount' => $setLimit,
            'amount' =>  $amount,
            'used' => $request->amount != $checkVoucherExists->amount  ? 0 :   $checkVoucherExists->used,

            'group_customer' =>  $request->group_customer,
            'agency_type_id' =>  $request->agency_type_id,
            'agency_type_name' =>  $request->agency_type_name,

            'group_type_id' =>  $request->group_type_id,
            'group_type_name' =>  $request->group_type_name,

            'is_public' => filter_var($request->is_public, FILTER_VALIDATE_BOOLEAN),
            'is_use_once' => filter_var($request->is_use_once, FILTER_VALIDATE_BOOLEAN),

            'group_customers' => $group_customers,
            'group_types' => $group_types,
            'agency_types' => $agency_types,
        ];

        $checkVoucherExists->update(
            $newData
        );

        if ($checkVoucherExists->is_use_once_code_multiple_time == false) {
            $query = VoucherCode::where('store_id', $request->store->id)
                ->where('voucher_id', $checkVoucherExists->id);

            $query->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ]);

            if (filter_var($request->is_end, FILTER_VALIDATE_BOOLEAN)) {
                $query->where('status', 0)
                    ->update([
                        'status' => 2
                    ]);
            }
        }


        //Add item line
        foreach ($productIds as $product_id) {
            $lineItem = ProductVoucher::create(
                [
                    'store_id' => $request->store->id,
                    'voucher_id' =>    $checkVoucherExists->id,
                    'product_id' => $product_id,
                ]
            );
        }

        if (isset($checkProductExists)) {
            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_UPDATE,
                TypeAction::FUNCTION_TYPE_PROMOTION,
                "Cập nhật voucher" . $checkVoucherExists->name,
                $checkProductExists->id,
                $checkProductExists->name
            );
        }


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Voucher::where('id', $checkVoucherExists->id)
                ->first()
        ], 201);
    }

    /**
     * Xem 1 voucher
     * @urlParam  store_code required Store code
     * @urlParam  voucher_id required Id Voucher
     */
    public function getOneVoucher(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $checkVoucherExists = Voucher::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if ($checkVoucherExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 400);
        }

        $checkVoucherExists->product_ids = $checkVoucherExists->products()->pluck('product_id')->toArray();

        $checkVoucherExists->products;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkVoucherExists,
        ], 200);
    }

    /**
     * Lấy danh sách sản phẩm trong voucher đang phát hành
     * @urlParam  store_code required Store code cần lấy.
     */
    public function getProductVoucher(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $search = StringUtils::convert_name_lowcase(request('search'));
        $voucher = Voucher::where('store_id', $request->store->id)
            ->where('id', $id)
            ->first();

        if ($voucher == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 400);
        }

        $products = $voucher->products()
            ->when($search, function ($query) use ($search) {
                $query->where('name_str_filter', 'LIKE', "%{$search}%");
            })
            ->paginate($request->limit ?: 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $products,
        ], 200);
    }

    /**
     * Xem tất cả voucher chuẩn vị và đang phát hàng
     * @urlParam  store_code required Store code
     */
    public function getAll(Request $request, $id)
    {
        $now = Helper::getTimeNowDateTime();

        $Vouchers = Voucher::where('store_id', $request->store->id,)
            ->where('is_end', false)
            ->where('end_time', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($voucher) {
                $voucher->products = [];
                return $voucher;
            });

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $Vouchers,
        ], 200);
    }
    /**
     * Xem tất cả voucher đã kết thúc
     * @queryParam  page Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)
     * @urlParam  store_code required Store code
     */
    public function getAllEnd(Request $request, $id)
    {

        $now = Helper::getTimeNowDateTime();
        $Vouchers = Voucher::where('store_id', $request->store->id,)
            ->where(function ($query) use ($now) {
                $query->where('is_end', '=', true)
                    ->orWhere('end_time', '<', $now);
            })
            ->orderBy('end_time', 'desc')
            ->paginate(20);

        foreach ($Vouchers as $voucher) {
            $voucher->products = [];
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $Vouchers,
        ], 200);
    }


    /**
     * xóa một chương trình voucher
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID voucher cần xóa thông tin.
     */
    public function deleteOneVoucher(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $voucherExists = Voucher::where(
            'store_id',
            $request->store->id
        )->where('id', $id)->first();

        if (empty($voucherExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 404);
        }

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_PROMOTION,
            "Xóa voucher " . $voucherExists->name,
            $voucherExists->id,
            $voucherExists->name
        );

        $voucherExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $voucherExists->id],
        ], 200);
    }
}
