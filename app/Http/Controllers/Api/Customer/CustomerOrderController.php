<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\AgencyUtils;
use App\Helper\BranchUtils;
use App\Helper\CollaboratorUtils;
use App\Helper\CustomerUtils;
use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\OtpUtils;
use App\Helper\PhoneUtils;
use App\Helper\Place;
use App\Helper\ProductUtils;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\StatusDefineCode;
use App\Helper\StringUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Jobs\SendEmailOrderCustomerJob;
use App\Models\Agency;
use App\Models\AgencyBonusStep;
use App\Models\BonusAgencyHistory;
use App\Models\CcartItem;
use App\Models\Combo;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Discount;
use App\Models\LineItem;
use App\Models\ListCart;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\BalanceCustomerService;
use App\Helper\PointCustomerUtils;
use App\Helper\RefundUtitls;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\TypeAction;
use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationStaffJob;
use App\Models\AgencyConfig;
use App\Models\BonusProduct;
use App\Models\CollaboratorsConfig;
use App\Models\CustomerVoucher;
use App\Models\HistorySms;
use App\Models\OtpUnit;
use App\Models\VoucherCode;
use App\Services\HistorySmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function PHPUnit\Framework\isEmpty;

/**
 * @group  Customer/Đơn hàng
 */

class CustomerOrderController extends Controller
{
    /**
     * Đặt hàng hoặc chỉnh đơn
     * Trường hợp lên đơn bằng oneCart thì sẽ thành cập nhật
     * 
     * 
     * @urlParam  store_code string required Store code
     * 
     * 
     * @bodyParam payment_method_id integer ID phương thức thanh toán là param payment_method_id ở API payment_methods
     * @bodyParam payment_partner_id integer ID Đối tác thanh toán là param id ở API payment_methods
     * @bodyParam partner_shipper_id integer required ID nhà giao hàng
     * @bodyParam shipper_type integer required (partner_shipper_id != null) Kiểu giao (0 tiêu chuẩn - 1 siêu tốc)
     * @bodyParam total_shipping_fee integer required (partner_shipper_id != null) Tổng tiền giao hàng
     * @bodyParam customer_address_id integer required ID địa chỉ khách hàng
     * @bodyParam customer_note string required Ghi chú khách hàng
     * @bodyParam collaborator_by_customer_id int customer  ID CTV
     * @bodyParam agency_by_customer_id int ID customer Đại lý
     * @bodyParam phone string Số điện thoại customer
     * @bodyParam name string Tên khách hàng
     * @bodyParam amount_money double Số tiền thanh toán
     * @bodyParam email string email khách hàng
     */
    public function create(Request $request)
    {
        $phone    = $request->phone;
        $from_pos  = $request->from_pos;
        $order_from  = $request->order_from;
        $name    = $request->name;
        $province  =  $request->province;
        $wards   = $request->wards;
        $district  = $request->district;
        $sex = $request->sex;
        $day_of_birth = $request->day_of_birth;
        $country   = $request->country;
        $address_detail   =  $request->address_detail;
        $email    =  $request->email;
        $customer_address_id =  $request->customer_address_id;
        $total_shipping_fee   =  $request->total_shipping_fee;
        $customer =  $request->customer;
        $customer_note =  $request->customer_note;

        $payment_method_id = $request->payment_method_id;
        $payment_partner_id = $request->payment_partner_id;
        $partner_shipper_id = $request->partner_shipper_id;
        $shipper_type = $request->shipper_type;

        if ($request->order_from == Order::ORDER_FROM_POS_IN_STORE) {
            $from_pos = true;
        } else {
            $from_pos = false;
        }

        $is_not_logged = !$customer && $request->order_from != Order::ORDER_FROM_POS_IN_STORE && $request->order_from != Order::ORDER_FROM_POS_DELIVERY  && $request->order_from != Order::ORDER_FROM_POS_SHIPPER;

        $has_cart_from_pos = false;
        //// Kiểm tra khi đẩy đơn hàng

        $cart_id = $request->route()->parameter('cart_id');

        $oneCart = null;
        if (!empty($cart_id)) {
            $oneCart = ListCart::where('store_id', $request->store->id)
                ->where('branch_id', $request->branch->id)
                ->where('id',  $cart_id)->first();

            if ($oneCart == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CART_EXISTS[0],
                    'msg' => MsgCode::NO_CART_EXISTS[1],
                ], 400);
            }

            $phone    =  $phone == null ?  $oneCart->customer_phone : $phone;
            $email = $oneCart->customer_email;
            $name    =   $oneCart->customer_name;
            $province  =    $oneCart->province;
            $wards   =   $oneCart->wards;
            $district  =  $oneCart->district;
            $country   =   $oneCart->country;
            $sex   =   $oneCart->customer_sex;
            $address_detail   =    $oneCart->address_detail;
            $day_of_birth = $oneCart->customer_day_of_birth;

            $customer_note  =  $oneCart->customer_note;

            $customer_address_id =  null;
            $total_shipping_fee   =    $oneCart->total_shipping_fee ?? 0;
            $customer =  $request->customer;

            $payment_method_id = $oneCart->payment_method_id ?? $payment_method_id;
            $partner_shipper_id =  $oneCart->partner_shipper_id;

            if ($oneCart->shipper_type) {
                $shipper_type =  $oneCart->shipper_type;
            }



            $has_cart_from_pos = true;
            // return response()->json(['oneCart' => 'true', 'data' => $oneCart]);
        }

        ////// ////// ////// ////// ////// ////// //////
        //Kiểm tra đơn này phải đơn edit không?
        $isEdit = ($oneCart != null && !empty($oneCart->edit_order_code));

        $device_id = request()->header('device_id');

        $used_bonus_products = CustomerCartController::handle_bonus_product($request, $cart_id);
        $allCart = CustomerCartController::all_items_cart($request, $cart_id);



        if ($has_cart_from_pos == true) {
            $cartInfo = CustomerCartController::data_response(
                $allCart,
                $request,
                $oneCart
            );
        } else {
            $cartInfo = CustomerCartController::data_response(
                $allCart,
                $request
            );
        }
        // return response()->json(['cartInfo' => 'true', 'data' => $cartInfo]);

        //Lấy danh sách item
        $line_items_in_time = json_encode($cartInfo['data']['line_items_in_time']);

        if (Cache::lock($line_items_in_time . $phone, 1)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                // 'msg' => "Đã đặt đơn",
                'msg' => "Thao tác quá nhanh",
            ], 400);
        }



        //check nợ
        if (
            $request->branch != null &&
            $has_cart_from_pos == true &&
            $request->amount_money < $cartInfo['data']['total_final'] &&
            $phone  == null &&
            ($oneCart != null && $oneCart->customer_id == null)
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::NO_CUSTOMER_DEBT_EXISTS[0],
                'msg' =>   MsgCode::NO_CUSTOMER_DEBT_EXISTS[1],
            ], 400);
        }

        if ($has_cart_from_pos == true &&  $isEdit == false) {


            if (!empty($phone)) {
                $phone = PhoneUtils::convert($phone);
                $valid = PhoneUtils::check_valid($phone);
                if ($valid == false && $phone != "----------") {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' =>  MsgCode::INVALID_PHONE_NUMBER[0],
                        'msg' =>  MsgCode::INVALID_PHONE_NUMBER[1],
                    ], 400);
                }
            }


            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' =>  MsgCode::INVALID_EMAIL[0],
                        'msg' =>  MsgCode::INVALID_EMAIL[1],
                    ], 400);
                }
            }

            //Xử lý customer vãng lai
            if ($oneCart->customer_id == null && $from_pos == true) {

                if (empty($phone)) {
                    $customerPassersby = CustomerUtils::getCustomerPassersby($request);
                    $phone    =   $customerPassersby->phone_number;
                    $name    =   $customerPassersby->name;
                }
            }
        }

        if ($cartInfo['success'] == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  $cartInfo['msg_code'],
                'msg' =>  $cartInfo['msg'],
            ], 400);
        }


        $lineRequests = $cartInfo['data']['line_items'];
        if (count($lineRequests) == 0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::NO_PRODUCT_EXISTS_IN_ORDER[0],
                'msg' =>  MsgCode::NO_PRODUCT_EXISTS_IN_ORDER[1],
            ], 400);
        }
        $is_all_bonus = true;
        //Kiểm tra nếu toàn sản phẩm thưởng
        if ($lineRequests != null || is_array($lineRequests)) {
            foreach ($lineRequests as $lineRequest) {
                $is_bonus = filter_var($lineRequest["is_bonus"] ?? false, FILTER_VALIDATE_BOOLEAN);;
                if ($is_bonus  == false) {
                    $is_all_bonus = false;
                    break;
                }
            }
        }
        if ($is_all_bonus == true) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::NO_PRODUCT_EXISTS_IN_ORDER[0],
                'msg' =>  MsgCode::NO_PRODUCT_EXISTS_IN_ORDER[1],
            ], 400);
        }
        //   //   //   //   //   //

        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];

        $lineItems = [];
        //Add line item
        if ($lineRequests != null || is_array($lineRequests)) {

            foreach ($lineRequests as $lineRequest) {
                $product_id = $lineRequest["product_id"];
                $quantity = $lineRequest["quantity"];
                $note = $lineRequest["note"];
                $distributes = $lineRequest["distributes"];
                $before_discount_price = $lineRequest['before_discount_price'];
                $item_price  = $lineRequest['item_price'];
                $element_distribute_id = $lineRequest['element_distribute_id'];
                $sub_element_distribute_id = $lineRequest['sub_element_distribute_id'];

                $checkProInven = DB::table('products')->select('name', 'id', 'quantity_in_stock', 'check_inventory')->where('store_id', $request->store->id)->where('id', $product_id)->first();

                if ($allow_semi_negative == false &&  $checkProInven->check_inventory == true) {
                    if ($product_id  != null &&  $element_distribute_id != null &&  $sub_element_distribute_id != null) {

                        $check_ele_inven = DB::table('element_distributes')->select('name', 'id', 'quantity_in_stock')->where('store_id', $request->store->id)
                            ->where('id',  $element_distribute_id)
                            ->where('product_id', $product_id)->first();


                        $check_sub_inven = DB::table('sub_element_distributes')->select('name', 'id', 'quantity_in_stock')->where('store_id', $request->store->id)
                            ->where('id',  $sub_element_distribute_id)
                            ->where('product_id', $product_id)->first();

                        if ($check_sub_inven != null  && $check_ele_inven != null &&  $check_sub_inven->quantity_in_stock < $quantity) {
                            if ($check_sub_inven->quantity_in_stock <= 0) {
                                return response()->json([
                                    'code' => 400,
                                    'success' => false,
                                    'msg_code' => MsgCode::ERROR[0],
                                    'msg' => $checkProInven->name . " (" . $check_ele_inven->name . " " . $check_sub_inven->name . ") đã hết hàng, vui lòng xóa khỏi giỏ hàng!",
                                ], 400);
                            } else if ($check_sub_inven->quantity_in_stock < $quantity) {
                                return response()->json([
                                    'code' => 400,
                                    'success' => false,
                                    'msg_code' => MsgCode::ERROR[0],
                                    'msg' => $checkProInven->name . " (" . $check_ele_inven->name . " " . $check_sub_inven->name . ") chỉ còn $check_sub_inven->quantity_in_stock sản phẩm, vui lòng xóa bớt giỏ hàng!",
                                ], 400);
                            }
                        }
                    } else
                    if ($product_id  != null &&  $element_distribute_id) {
                        $check_ele_inven = DB::table('element_distributes')->select('name', 'id', 'quantity_in_stock')->where('store_id', $request->store->id)
                            ->where('id',  $element_distribute_id)
                            ->where('product_id', $product_id)->first();


                        if ($check_ele_inven != null &&  $check_ele_inven->quantity_in_stock < $quantity) {

                            if ($check_ele_inven->quantity_in_stock <= 0) {
                                return response()->json([
                                    'code' => 400,
                                    'success' => false,
                                    'msg_code' => MsgCode::ERROR[0],
                                    'msg' => $checkProInven->name . " (" . $check_ele_inven->name . ") đã hết hàng, vui lòng xóa khỏi giỏ hàng!",
                                ], 400);
                            } else if ($check_ele_inven->quantity_in_stock < $quantity) {
                                return response()->json([
                                    'code' => 400,
                                    'success' => false,
                                    'msg_code' => MsgCode::ERROR[0],
                                    'msg' => $checkProInven->name . " (" . $check_ele_inven->name . ") chỉ còn $check_ele_inven->quantity_in_stock sản phẩm, vui lòng xóa bớt giỏ hàng!",
                                ], 400);
                            }
                        }
                    } else
                    if ($product_id  != null) {
                        if ($checkProInven != null &&  $checkProInven->quantity_in_stock < $quantity) {
                            if ($checkProInven->quantity_in_stock <= 0) {
                                return response()->json([
                                    'code' => 400,
                                    'success' => false,
                                    'msg_code' => MsgCode::ERROR[0],
                                    'msg' => "(" . $checkProInven->name . ") đã hết hàng, vui lòng xóa khỏi giỏ hàng!",
                                ], 400);
                            } else if ($checkProInven->quantity_in_stock < $quantity) {
                                return response()->json([
                                    'code' => 400,
                                    'success' => false,
                                    'msg_code' => MsgCode::ERROR[0],
                                    'msg' => "(" . $checkProInven->name . ") chỉ còn $checkProInven->quantity_in_stock sản phẩm, vui lòng xóa bớt giỏ hàng!",
                                ], 400);
                            }
                        }
                    }
                }



                if (
                    !isset($product_id) || !isset($quantity)
                    || $quantity <= 0
                ) {

                    if (!isset($product_id)) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_LINE_ITEM[0],
                            'msg' => MsgCode::INVALID_LINE_ITEM[1],
                        ], 400);
                    }


                    if (!isset($quantity) || $quantity <= 0) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_LINE_ITEM_QUANTITY[0],
                            'msg' => MsgCode::INVALID_LINE_ITEM_QUANTITY[1],
                        ], 400);
                    }
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

                array_push($lineItems, (object)[
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'note' => $note,
                    'distributes' => $distributes,
                    'before_discount_price' => $before_discount_price,
                    'item_price' => $item_price,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'is_bonus' => $lineRequest["is_bonus"] ??  false,
                    'parent_cart_item_ids' => $lineRequest["parent_cart_item_ids"] ??  false,
                    'bonus_product_name' => $lineRequest["bonus_product_name"] ?? ""
                ]);
            }
        }

        //Check duplicate
        foreach ($lineItems as $item) {
            $dup = 0;
            foreach ($lineItems as $item2) {
                if ($item->is_bonus == false &&  $item2->is_bonus == false && $item->product_id == $item2->product_id && $item->distributes == $item2->distributes) {
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


        //Kiem tra la dai ly
        $is_agency =  false;

        if ($request->customer != null) {
            $agency = Agency::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first();

            if ($agency  != null && $agency->status == 1 && $request->customer->is_agency == true) {
                $is_agency =  true;
            }
        }


        //Đã đăng nhập
        if ($request->customer != null && $request->order_from != Order::ORDER_FROM_POS_IN_STORE && $request->order_from != Order::ORDER_FROM_POS_DELIVERY  && $request->order_from != Order::ORDER_FROM_POS_SHIPPER) {


            //Handle Address
            $idAddress = $customer_address_id;

            if (empty($idAddress)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ADDRESS_SELECTED[0],
                    'msg' => MsgCode::NO_ADDRESS_SELECTED[1],
                ], 404);
            }

            $addressExists = CustomerAddress::where(
                'store_id',
                $request->store->id
            )
                ->where(
                    'customer_id',
                    $request->customer->id
                )
                ->where('id', $idAddress)->first();

            if (empty($addressExists)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_ADDRESS[0],
                    'msg' => MsgCode::INVALID_ADDRESS[1],
                ], 400);
            }

            $addressExists = $addressExists->toArray();
        } else { //ko đăng nhập

            if ($from_pos != true) {
                if ($phone == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                        'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
                    ], 400);
                }

                if ($name == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                        'msg' => MsgCode::NAME_IS_REQUIRED[1],
                    ], 400);
                }

                if (Place::getNameProvince($province) == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_PROVINCE[0],
                        'msg' => MsgCode::INVALID_PROVINCE[1],
                    ], 400);
                }

                if (Place::getNameDistrict($district) == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DISTRICT[0],
                        'msg' => MsgCode::INVALID_DISTRICT[1],
                    ], 400);
                }

                if (Place::getNameWards($wards) == null) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_WARDS[0],
                        'msg' => MsgCode::INVALID_WARDS[1],
                    ], 400);
                }
            }


            $addressExists = [];

            $addressExists['country'] = $country;
            $addressExists['province'] = $province;
            $addressExists['district'] = $district;
            $addressExists['wards'] = $wards;
            $addressExists['name'] = $name;
            $addressExists['phone'] = $phone;
            $addressExists['email'] = $email;
            $addressExists['address_detail'] = $address_detail;
        }




        if ($phone != null) {
            //Tạo customer ảo
            if ($customer == null) {

                $c = Customer::where('phone_number', $phone)
                    ->where('store_id', $request->store->id)->first();

                if ($c != null) {
                    $request->customer = $c;
                } else {

                    $cCreate = Customer::create(
                        [
                            'area_code' => '+84',
                            'name' => $name,
                            'name_str_filter' => StringUtils::convert_name_lowcase($name),
                            'phone_number' => $phone,
                            'email' => $addressExists['email'] ?? null,
                            'store_id' => $request->store->id,
                            'password' => bcrypt('DOAPP_BCRYPT_PASS'),
                            'official' => false,

                            "sex" => $sex,
                            "date_of_birth" => $day_of_birth,

                            'address_detail' =>  $address_detail,
                            "province" => $province,
                            "district" => $district,
                            "wards" =>  $wards,

                            "province_name" => Place::getNameProvince($province),
                            "district_name" => Place::getNameDistrict($district),
                            "wards_name" => Place::getNameWards($wards),
                        ]
                    );

                    $request->customer = Customer::where('id',  $cCreate->id)->first();
                }
            }
        }


        $order_code = Helper::getRandomOrderString();

        //Kiểm tra cộng thêm SL áp dụng
        $used_discount = $cartInfo['data']['used_discount'];
        $used_combos = $cartInfo['data']['used_combos'];
        $used_voucher = $cartInfo['data']['used_voucher'];


        if (isset($used_discount) && is_array($used_discount)) {
            foreach ($used_discount as $discount) {
                $dis =   Discount::where('store_id', $request->store->id)
                    ->where('id', $discount['id'])->first();
                if ($dis != null) {
                    $dis->update(
                        [
                            'used' => ($dis->used + 1)
                        ]
                    );
                }
            }
        }

        if (isset($used_combos) && is_array($used_combos)) {
            foreach ($used_combos as $combo) {
                $com =   Combo::where('store_id', $request->store->id)
                    ->where('id', $combo['combo']['id'])->first();
                if ($com != null) {
                    $com->update(
                        [
                            'used' => ($com->used + 1)
                        ]
                    );
                }
            }
        }

        if (isset($used_voucher) && isset($used_voucher['id'])) {
            $vou =   Voucher::where('store_id', $request->store->id)
                ->where('id', $used_voucher['id'])->first();
            if ($vou != null) {
                $vou->update(
                    [
                        'used' => ($vou->used + 1)
                    ]
                );
            }
        }

        if (isset($used_bonus_products) && is_array($used_bonus_products)) {
            foreach ($used_bonus_products as $used_bonus_product) {
                $bp =   BonusProduct::where('store_id', $request->store->id)
                    ->where('id', $used_bonus_product['bonus_product']['id'])->first();
                if ($bp != null) {
                    $bp->update(
                        [
                            'used' => ($bp->used + 1)
                        ]
                    );
                }
            }
        }

        $used_discount = json_encode($used_discount);
        $used_combos = json_encode($used_combos);
        $used_voucher = json_encode($used_voucher);
        $used_bonus_products = json_encode($used_bonus_products);




        ////    ////   ////    ////   ////    ////   ////    ////   ////    ////   
        $total_final = $cartInfo['data']['total_final'];
        $total_final_before_override = $cartInfo['data']['total_final_before_override'];
        $total_commission_order_for_customer = $cartInfo['data']['total_commission_order_for_customer'];


        if ($has_cart_from_pos == true) {
            if ($oneCart->customer_id != null) {
                $request->customer = Customer::where('id', $oneCart->customer_id)->first();
            }
        }


        $branch_id = null;

        if ($request->branch_id) {
            $branch_id = $request->branch_id;
        } else if ($request->branch != null) {
            $branch_id = $request->branch->id;
        } else {
            $branch_id = BranchUtils::getBranchDefaultOrderOnline($request->store->id)->id;
        }

        $collaborator_by_customer_id = null;
        $collaborator_by_customer_referral_id = null;

        if ($request->customer != null && $request->customer->id != null && CollaboratorUtils::isCollaborator($request->customer->id, $request->store->id)) {
            $collaborator_by_customer_id = $request->customer->id;
        } else {

            if (CollaboratorUtils::isCollaborator($request->collaborator_by_customer_id, $request->store->id)) {

                $collaborator_by_customer_id = $request->collaborator_by_customer_id;
            }
        }

        if ($request->customer != null && $request->customer->id != null && $request->customer->referral_phone_number) {

            $customerCollaborator = Customer::where('store_id', $request->store->id)
                ->where('phone_number', $request->customer->referral_phone_number)
                ->first();

            if ($customerCollaborator && CollaboratorUtils::isCollaborator($customerCollaborator->id, $request->store->id)) {
                $collaborator_by_customer_referral_id = $customerCollaborator->id;
            }
        }

        $agency_ctv_by_customer_id = null;

        if (AgencyUtils::isAgencyByCustomerId($request->collaborator_by_customer_id, $request->store->id)) {
            $agency_ctv_by_customer_id = $request->collaborator_by_customer_id;
        }

        $agency_ctv_by_customer_referral_id = null;

        if ($request->customer != null && $request->customer->id != null) {

            if ($request->customer->referral_phone_number) {

                $customerAgency = Customer::where('store_id', $request->store->id)
                    ->where('phone_number', $request->customer->referral_phone_number)
                    ->first();

                if ($customerAgency) {

                    if (AgencyUtils::isAgencyByCustomerId($customerAgency->id)) {

                        $agency_ctv_by_customer_referral_id = $customerAgency->id;
                    }
                }
            }
        }


        $sale_by_staff_id = null;
        if ($request->customer != null && $request->customer->id != null) {
            if (AgencyUtils::isAgencyByCustomerId($request->customer->id, $request->store->id)) {
                if ($request->customer->id == $request->collaborator_by_customer_id) {
                    $agency_ctv_by_customer_id = null;
                }
            }
            $sale_by_staff_id =  $request->customer->sale_staff_id;
        }

        if ($request->customer != null && $request->customer->id != null) {
            if (AgencyUtils::isAgencyByCustomerId($request->customer->id, $request->store->id)) {
                if ($request->is_order_for_customer) {
                    $agency_ctv_by_customer_id = $request->customer->id;
                }
            }
        }


        if ($total_shipping_fee != null &&  $total_shipping_fee > 0) {

            $total_shipping_fee  =  $cartInfo['data']['total_shipping_fee'];
        }

        $cod = $total_final - $total_shipping_fee;

        if ($cartInfo['data']['ship_discount_amount'] > 0) {
            $cod = $total_final;
        }


        $dataOrder =  [
            'store_id' => $request->store->id,
            'customer_id' => $request->customer == null ? null : $request->customer->id,
            'phone_number' => $request->customer != null ? $request->customer->phone_number :  $addressExists['phone'],
            'order_code' => $order_code,
            'order_status' =>  $from_pos == true ? StatusDefineCode::RECEIVED_PRODUCT : 0,
            'payment_status' => $from_pos == true ? StatusDefineCode::UNPAID : 0,
            'logged' => ($addressExists['id'] ?? null) != null ? true : false,
            //cần truyền lên
            'payment_method_id' => $payment_method_id,

            'ship_speed_code' => !empty($request->ship_speed_code) ? $request->ship_speed_code : $shipper_type,
            'description_shipper' => $request->description_shipper,

            'payment_partner_id' => $payment_partner_id,
            'partner_shipper_id' => $partner_shipper_id,
            'shipper_type' => 0, // Luồng mới dùng ship_speed_code  

            'ship_discount_amount' => $cartInfo['data']['ship_discount_amount'],

            'balance_collaborator_used' => $cartInfo['data']['balance_collaborator_used'],
            'balance_agency_used' => $cartInfo['data']['balance_agency_used'],

            'is_use_points' => (($cartInfo['data']['bonus_points_amount_used'] ?? 0) + ($cartInfo['data']['total_points_used'] ?? 0)) > 0 ? true : false,
            'is_order_for_customer' => $cartInfo['data']['is_order_for_customer'] ? true : false,
            'bonus_points_amount_used' => $cartInfo['data']['bonus_points_amount_used'] + $cartInfo['data']['points_amount_used_edit_order'],
            'total_points_used' => $cartInfo['data']['total_points_used'] + $cartInfo['data']['points_total_used_edit_order'],

            'vat' => $cartInfo['data']['vat'],
            'cod' => $cod,
            'total_before_discount' => $cartInfo['data']['total_before_discount'],
            'combo_discount_amount' => $cartInfo['data']['combo_discount_amount'],
            'product_discount_amount' => $cartInfo['data']['product_discount_amount'],
            'voucher_discount_amount' => $cartInfo['data']['voucher_discount_amount'],
            'share_collaborator' => $cartInfo['data']['share_collaborator'],
            'share_agency' => $cartInfo['data']['share_agency'],
            'point_for_agency' => $cartInfo['data']['point_for_agency'],
            'discount' => $cartInfo['data']['discount'],
            'total_after_discount' => $cartInfo['data']['total_after_discount'],
            'package_weight' => $cartInfo['data']['package_weight'] ?? 0,

            'total_final' => $total_final,
            'total_final_before_override' => $total_final_before_override,
            'total_commission_order_for_customer' => $total_commission_order_for_customer,
            'remaining_amount' => $total_final,

            'total_shipping_fee' =>  $total_shipping_fee,

            'used_discount' =>   $used_discount,
            'used_combos' =>   $used_combos,
            'used_voucher' =>  $used_voucher,
            'used_bonus_products' =>  $used_bonus_products,

            'line_items_in_time' => $line_items_in_time,

            'customer_name' => $addressExists['name'],
            'customer_country' => $addressExists['country'],
            'customer_province' => $addressExists['province'],
            'customer_district' => $addressExists['district'],
            'customer_wards' => $addressExists['wards'],

            "customer_province_name" =>   Place::getNameProvince($addressExists['province'] ?? null),
            "customer_district_name" =>  Place::getNameDistrict($addressExists['district'] ?? null),
            "customer_wards_name" =>  Place::getNameWards($addressExists['wards'] ?? null),

            'customer_village' => $addressExists['village'] ?? null,
            'customer_postcode' => $addressExists['postcode'] ?? null,
            'customer_email' => $addressExists['email'] ?? null,
            'customer_phone' => $addressExists['phone'],
            'customer_address_detail' => $addressExists['address_detail'] ?? null,
            'sale_by_staff_id' => $sale_by_staff_id,

            //cần truyền lên
            'customer_note' =>  $customer_note,

            'branch_id' =>  $branch_id,
            'from_pos' =>  filter_var($from_pos, FILTER_VALIDATE_BOOLEAN),
            'order_from' => $order_from ?? 0,
            'created_by_user_id' => $request->user != null ? $request->user->id : null,
            'created_by_staff_id' => $request->staff != null ? $request->staff->id : null,
            //
            'collaborator_by_customer_id' =>   $collaborator_by_customer_id,
            'collaborator_by_customer_referral_id' =>   $collaborator_by_customer_referral_id,
            'agency_ctv_by_customer_id' => $agency_ctv_by_customer_id,
            'agency_ctv_by_customer_referral_id' => $agency_ctv_by_customer_referral_id,
            'agency_by_customer_id' =>   $is_agency == true ?  $request->customer->id : null,
        ];

        if ($isEdit) {
            $orderCreated = Order::where('store_id', $request->store->id)
                ->where('order_code', $oneCart->edit_order_code)->first();

            $dataOrder['ship_speed_code'] = $dataOrder['ship_speed_code'] ?? $orderCreated->ship_speed_code;
            $dataOrder['order_code'] = $oneCart->edit_order_code;
            $dataOrder['customer_id'] = $orderCreated->customer_id;
            $dataOrder['phone_number'] = $orderCreated->phone_number;

            $dataOrder['payment_status'] = $orderCreated->payment_status;
            $dataOrder['order_status'] = $orderCreated->order_status;
            $dataOrder['order_from'] = $orderCreated->order_from;
            $dataOrder['created_by_user_id'] = $orderCreated->created_by_user_id;
            $dataOrder['created_by_staff_id'] = $orderCreated->created_by_staff_id;

            $orderCreated->update($dataOrder);

            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $orderCreated->customer_id,
                "Đơn hàng " . $oneCart->edit_order_code,
                "Đã được thay đổi, nhấn để xem chi tiết đơn",
                TypeFCM::ORDER_STATUS,
                $oneCart->edit_order_code
            );
            // SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderCreated);
        } else {
            $orderCreated = Order::create(
                $dataOrder
            );
        }

        if ($cartInfo && $cartInfo['data'] && $cartInfo['data']['used_voucher'] && $cartInfo['data']['used_voucher']['is_use_once'] && $request->customer) {
            CustomerVoucher::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' => $request->customer->id,
                    'voucher_id' => $cartInfo['data']['used_voucher']['id']
                ]
            );
        }

        if ($cartInfo && $cartInfo['data'] && $cartInfo['data']['used_voucher'] && $cartInfo['data']['used_voucher']['is_use_once_code_multiple_time'] == false && $cartInfo['data']['used_voucher']['voucher_code'] && $request->customer) {
            VoucherCode::where('store_id', $request->store->id)
                ->where('voucher_id', $cartInfo['data']['used_voucher']['id'])
                ->where('id', $cartInfo['data']['used_voucher']['voucher_code']['id'])
                ->update(
                    [
                        'customer_id' => $request->customer->id,
                        'voucher_id' => $cartInfo['data']['used_voucher']['id'],
                        'status' => 1,
                        'use_time' => Helper::getTimeNowDateTime()
                    ]
                );
        }


        $order_status  =  $orderCreated == null ? null :  $orderCreated->order_status;
        if ($request->order_from == Order::ORDER_FROM_POS_IN_STORE || $request->order_from == Order::ORDER_FROM_POS_DELIVERY  || $request->order_from == Order::ORDER_FROM_POS_SHIPPER) {

            if ($request->amount_money >=  $total_final) {
                $request->amount_money = $total_final;
            }

            ////    ////   ////    ////   ////    ////   ////    ////   
            //0 chưa thanh toán, 1 thanh toán 1 phần, 2 đã thanh toán
            $paid = 0;
            $payment_status = StatusDefineCode::UNPAID;

            if ($request->amount_money >=  $total_final) {
                $paid = $request->amount_money;
                $payment_status = StatusDefineCode::PAID;
            } else if ($request->amount_money  > 0 && $request->amount_money <  $total_final) {
                $paid = $request->amount_money;
                $payment_status = StatusDefineCode::PARTIALLY_PAID;
            }


            $remaining_amount =  round($orderCreated->remaining_amount - $paid);

            //mặc định trạng thái
            if ($isEdit == false) {
                if ($request->order_from == Order::ORDER_FROM_POS_IN_STORE) {
                    $order_status  = StatusDefineCode::RECEIVED_PRODUCT;
                }
                if ($request->order_from == Order::ORDER_FROM_POS_DELIVERY || $request->order_from == Order::ORDER_FROM_POS_SHIPPER) {
                    $order_status  = StatusDefineCode::PACKING;
                }


                if ($payment_status == StatusDefineCode::PAID && $request->order_from == Order::ORDER_FROM_POS_IN_STORE) {
                    $order_status = StatusDefineCode::COMPLETED;
                }

                if ($payment_status == StatusDefineCode::PAID &&  $request->customer != null) {
                    PointCustomerUtils::bonus_point_from_order($request, $orderCreated);
                }
            }

            $codPaid = $total_final - $total_shipping_fee - $paid > 0  ? $total_final - $total_shipping_fee - $paid : 0;

            if ($cartInfo['data']['ship_discount_amount'] > 0) {
                $codPaid = $total_final - $paid > 0  ? $total_final - $paid : 0;
            }

            $orderCreated->update([
                "order_status" =>   $order_status,
                "payment_status" => $payment_status,
                'remaining_amount' =>   round($orderCreated->remaining_amount - $paid),
                'cod' => $codPaid
            ]);
        }

        if ($isEdit == false) {
            //Kiểm tra collaborator
            $collaborator_by_customer_id = null;
            //trước tiên kiểm tra đơn hàng có xuất phát từ id customer đã làm ctv ko (phải thì sét lại id ctv cho đơn)
            if (CollaboratorUtils::isCollaborator($orderCreated->customer_id, $request->store->id) == true) {
                $orderCreated->update([
                    'collaborator_by_customer_id' => $orderCreated->customer_id
                ]);
            } else if (AgencyUtils::isAgencyByCustomerId($orderCreated->customer_id == true)) {
                $orderCreated->update([
                    'agency_ctv_by_customer_id' => $orderCreated->customer_id
                ]);
            }


            RevenueExpenditureUtils::auto_add_expenditure_order($orderCreated, $request);
            RevenueExpenditureUtils::auto_add_revenue_order($orderCreated, $request);

            ////  ////   ////  
            //Kiểm tra có phần thưởng
            if ($is_agency == true) {
                if (isset($cartInfo['data']['bonus_agency'])) {
                    $bonus_agency = $cartInfo['data']['bonus_agency'];
                    if (isset($bonus_agency['config']) && isset($bonus_agency['step_bonus']) && count($bonus_agency['step_bonus']) > 0) {
                        foreach ($bonus_agency['step_bonus'] as $step) {
                            if ($step['active'] == true) {
                                if (BonusAgencyHistory::create([
                                    'store_id' =>  $request->store->id,
                                    'order_id' => $orderCreated->id,
                                    'customer_id' => $request->customer->id,
                                    "threshold" => $step['threshold'],
                                    "reward_name" => $step['reward_name'],
                                    "reward_description" => $step['reward_description'],
                                    "reward_image_url" => $step['reward_image_url'],
                                    "reward_value" => $step['reward_value'],
                                    "limit" => $step['limit'],
                                ]));
                                $stepUp = AgencyBonusStep::where('id', $step['id'])->first();

                                if ($stepUp != null) {
                                    $stepUp->update([
                                        "limit" => $step['limit'] - 1
                                    ]);
                                }
                            }
                        };
                    }
                }
            }


            if ($order_status ==  StatusDefineCode::COMPLETED || $order_status == StatusDefineCode::RECEIVED_PRODUCT) {
                //Xử lý trừ kho
                foreach ($lineItems as $item) {

                    $item->has_subtract_inventory = true;

                    InventoryUtils::add_sub_stock_by_id(
                        $request->store->id,
                        $branch_id,
                        $item->product_id,
                        $item->element_distribute_id,
                        $item->sub_element_distribute_id,
                        - ($item->quantity),
                        InventoryUtils::TYPE_EXPORT_ORDER_STOCK,
                        $orderCreated->id,
                        $orderCreated->order_code
                    );
                }
            }


            $total_cost_of_capital = 0;
            //Add item line
            foreach ($lineItems as $item) {

                $cost_of_capital = 0;

                if ($branch_id) {
                    $distribute_data =  InventoryUtils::get_stock_by_distribute_by_id(
                        $request->store->id,
                        $branch_id,
                        $item->product_id,
                        $item->element_distribute_id,
                        $item->sub_element_distribute_id,
                    );

                    $cost_of_capital = $distribute_data['cost_of_capital'] ?? 0;
                    $total_cost_of_capital += ($cost_of_capital * $item->quantity);
                }

                $lineItem = LineItem::create(
                    [
                        'store_id' => $request->store->id,
                        'customer_id' => $request->customer == null ? null : $request->customer->id,
                        'phone_number' => $request->customer != null ? $request->customer->phone_number :  $addressExists['phone'],
                        'order_id' =>  $orderCreated->id,
                        'before_discount_price' => $item->before_discount_price,
                        // 'price_before_override' => $item->price_before_override,
                        'item_price' => $item->item_price,
                        'product_id' => $item->product_id,
                        'branch_id' => $branch_id,
                        'element_distribute_id' => $item->element_distribute_id,
                        'sub_element_distribute_id' => $item->sub_element_distribute_id,
                        'quantity' => $item->quantity,
                        'note' => $item->note,
                        'is_bonus' => $item->is_bonus,
                        'parent_cart_item_ids' => $item->parent_cart_item_ids,
                        'bonus_product_name' => $item->bonus_product_name,
                        'distributes' => is_string($item->distributes) ? $item->distributes : json_encode($item->distributes),
                        'cost_of_capital' =>  $cost_of_capital,
                        'has_subtract_inventory' => filter_var($item->has_subtract_inventory ?? false, FILTER_VALIDATE_BOOLEAN)
                    ]
                );


                $productUpdateSold = Product::where('id', $item->product_id)->first();
                if ($productUpdateSold != null) {
                    $productUpdateSold->update([
                        'sold' =>  $item->quantity + $productUpdateSold->sold
                    ]);
                }
            }

            $orderCreated->update([
                'total_cost_of_capital' => $total_cost_of_capital
            ]);

            PushNotificationUserJob::dispatch(
                $request->store->id,
                $request->store->user_id,
                'Shop ' . $request->store->name,
                'Vừa có đơn hàng mới ' . $order_code,
                TypeFCM::NEW_ORDER,
                $order_code,
                $branch_id,
            );

            PushNotificationStaffJob::dispatch(
                $request->store->id,
                'Shop ' . $request->store->name,
                'Vừa có đơn hàng mới ' . $order_code,
                TypeFCM::NEW_ORDER,
                $order_code,
                $branch_id,
                null,
            );

            if ($has_cart_from_pos == true) {
                SaveOperationHistoryUtils::save(
                    $request,
                    TypeAction::OPERATION_ACTION_ADD,
                    TypeAction::FUNCTION_TYPE_ORDER,
                    "Lên đơn hàng tại POS: " . $order_code,
                    $orderCreated->id,
                    $orderCreated->order_code
                );
            }


            //Gửi email
            $emails = ["dev.ikitech@gmail.com"];

            if (filter_var($addressExists['email'], FILTER_VALIDATE_EMAIL)) {
                array_push($emails, $addressExists['email']);
            }

            if ($request->customer != null && filter_var($request->customer->email, FILTER_VALIDATE_EMAIL)) {
                array_push($emails, $request->customer->email);
            }

            $emails  = array_unique($emails);
            if (count($emails) > 0) {

                SendEmailOrderCustomerJob::dispatch(
                    $emails,
                    $request->store,
                    $order_code
                );
            }
        }

        if ($isEdit == true) {
            LineItem::where('store_id', $request->store->id)->where('order_id', $orderCreated->id)->delete();
            //Add item line
            foreach ($lineItems as $item) {

                $lineItem = LineItem::create(
                    [
                        'store_id' => $request->store->id,
                        'customer_id' => $request->customer == null ? null : $request->customer->id,
                        'phone_number' => $request->customer != null ? $request->customer->phone_number :  $addressExists['phone'],
                        'order_id' =>  $orderCreated->id,
                        'before_discount_price' => $item->before_discount_price,
                        // 'price_before_override' => $item->price_before_override,
                        'item_price' => $item->item_price,
                        'product_id' => $item->product_id,
                        'branch_id' => $branch_id,
                        'element_distribute_id' => $item->element_distribute_id,
                        'sub_element_distribute_id' => $item->sub_element_distribute_id,
                        'quantity' => $item->quantity,
                        'note' => $item->note,
                        'is_bonus' => $item->is_bonus,
                        'parent_cart_item_ids' => $item->parent_cart_item_ids,
                        'bonus_product_name' => $item->bonus_product_name,
                        'distributes' => is_string($item->distributes) ? $item->distributes : json_encode($item->distributes),
                        'cost_of_capital' =>  0,
                        'has_subtract_inventory' => true
                    ]
                );
            }

            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_UPDATE,
                TypeAction::FUNCTION_TYPE_ORDER,
                "Cập nhật đơn hàng tại POS: " . $order_code,
                $orderCreated->id,
                $orderCreated->order_code
            );
        }


        if ($request->customer != null) {
            //Kiểm tra trừ xu
            $point_used = $orderCreated->total_points_used;
            $is_use_points = $orderCreated->is_use_points;
            $bonus_points_amount_used = $orderCreated->bonus_points_amount_used;


            //Trừ xu khi đã sử dụng
            if ($bonus_points_amount_used > 0 && $point_used > 0 &&  $is_use_points == true) {
                $point_used = -$point_used;
                PointCustomerUtils::add_sub_point(
                    PointCustomerUtils::USE_POINT_IN_ORDER,
                    $request->store->id,
                    $request->customer->id,
                    (int)$point_used,
                    $orderCreated->id,
                    $orderCreated->order_code
                );
            }

            //Kiểm tra trừ số dư CTV
            $balance_collaborator_used = $cartInfo['data']['balance_collaborator_used'];
            if ($balance_collaborator_used > 0) {
                BalanceCustomerService::change_balance_collaborator(
                    $request->store->id,
                    $request->customer->id,
                    BalanceCustomerService::USE_BALANCE_ORDER,
                    -$balance_collaborator_used,
                    $orderCreated->id,

                );
            }

            //Kiểm tra trừ số dư Agency
            $balance_agency_used = $cartInfo['data']['balance_agency_used'];
            if ($balance_agency_used > 0) {
                BalanceCustomerService::change_balance_agency(
                    $request->store->id,
                    $request->customer->id,
                    BalanceCustomerService::USE_BALANCE_ORDER,
                    -$balance_agency_used,
                    $orderCreated->id,

                );
            }
        }


        $list_cart_id = $cart_id == null ? null : $cart_id;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;


        $deleteCarts =  CcartItem::allItem($list_cart_id,  $request)
            ->delete();


        if ($oneCart != null) {
            $oneCart->update([
                'name' => null,

                'is_use_points' => false,
                'is_use_balance_collaborator' => false,
                'is_use_balance_agency' => false,

                'partner_shipper_id' => null,
                'shipper_type' =>  null,
                'total_shipping_fee' =>  0,
                'discount' =>  0,
                'customer_address_id' =>  null,
                'customer_note' =>  "",
                'customer_phone' =>  null,
                'customer_name' =>  null,
                'customer_email' =>  null,

                'customer_id' => null,

                'address_detail' => null,
                'province' => null,
                'district' => null,
                'wards' => null,
                'code_voucher' => null
            ]);

            if ($oneCart->is_default == false) {
                $oneCart->delete();
            }
        }

        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderCreated);
        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::NEW_ORDER,   $orderCreated);

        //TH chưa đăng nhập và nhập SĐT đại lý
        $orderExists = $orderCreated;
        $customer_agency_exists = Customer::where('id', $orderExists->customer_id)
            ->where('store_id', $request->store->id)
            ->where('is_agency', true)
            ->first();

        if ($is_not_logged && $customer_agency_exists != null && AgencyUtils::isAgencyByCustomerId($orderExists->customer_id) == true) {

            $config_agency_exists = AgencyConfig::where('store_id', $request->store->id)->first();

            if ($config_agency_exists != null && $config_agency_exists->percent_agency_t1 > 0) {
                //Cộng tiền chia sẻ cho ctv t2
                if ($config_agency_exists->bonus_type_for_ctv_t2 == 1) {

                    /////////Tính chia sẻ cho Agency
                    $allCart =  $orderExists->line_items;
                    $share_agency  = 0;

                    foreach ($allCart as $lineItem) {

                        if ($lineItem->is_bonus == false) {
                            $agency = AgencyUtils::getAgencyByCustomerId($customer_agency_exists->id);

                            if ($agency != null) {
                                $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                                $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                            }
                        }
                    }

                    $share_agency = $share_agency * ($config_agency_exists->percent_agency_t1 / 100);
                } else {
                    $share_agency = ($orderExists->total_after_discount + $orderExists->bonus_points_amount_used + $orderExists->balance_agency_used + $orderExists->balance_collaborator_used) * ($config_agency_exists->percent_agency_t1 / 100);
                }

                $orderExists->update([
                    'share_collaborator' => 0,
                    'share_collaborator_referen' => 0,
                    'collaborator_by_customer_id' => null,
                    'collaborator_by_customer_referral_id' => null,
                    'share_agency' => 0,
                    'agency_ctv_by_customer_id' => null,
                    'share_agency_referen' => $share_agency,
                    'agency_ctv_by_customer_referral_id' => $customer_agency_exists->id,
                ]);
            }
        } else {
            //Tính tiền cho ctv
            //Cộng tiền chia sẻ cho ctv t1 (là CTV giới thiệu CTV chính của đơn)
            $configExists = CollaboratorsConfig::where(
                'store_id',
                $request->store->id
            )->first();

            if ($configExists != null) {
                if ($configExists->percent_collaborator_t1 > 0) {

                    //TH1 CTV đặt hộ hoặc Dropship
                    //trước tiên kiểm tra đơn hàng có xuất phát từ id customer đã làm ctv ko (phải thì sét lại id ctv cho đơn)
                    if (CollaboratorUtils::isCollaborator($orderExists->customer_id, $request->store->id) == true) {
                        $orderExists->update([
                            'collaborator_by_customer_id' => $orderExists->customer_id
                        ]);
                    } else {
                        //TH2 tìm người giới thiệu qua khách mua qua sdt (ưu tiên qua sdt)
                        $customer_mh = Customer::where('id', $orderExists->customer_id)->where(
                            'store_id',
                            $request->store->id
                        )->first();


                        if ($customer_mh != null) {
                            //Tìm ctv t2 (là CTV giới thiệu CTV chính của đơn)
                            $customer_gt = Customer::where('phone_number', $customer_mh->referral_phone_number)
                                ->where(
                                    'store_id',
                                    $request->store->id
                                )->where('is_collaborator', true)->first();


                            if ($customer_gt != null) {
                                $orderExists->update([
                                    'collaborator_by_customer_id' => $customer_gt->id
                                ]);
                            }
                        }
                    }

                    //nếu gián tiếp = trực tiếp
                    if ($orderExists->collaborator_by_customer_referral_id  != null && $orderExists->collaborator_by_customer_id == $orderExists->collaborator_by_customer_referral_id) {
                        $orderExists->update([
                            'share_collaborator_referen' => 0,
                            'collaborator_by_customer_referral_id' => null
                        ]);
                    }

                    if ($orderExists->collaborator_by_customer_id == null) {
                        $orderExists->update([
                            'share_collaborator' => 0
                        ]);
                    }

                    if ($orderExists->collaborator_by_customer_id != null) {
                        //Tìm CTV F1 đã giới thiệu F2 đi gt hoặc mua hàng
                        $customer_f1 = null;
                        $customer_f2 = Customer::where('id', $orderExists->collaborator_by_customer_id)->where(
                            'store_id',
                            $request->store->id
                        )->where('is_collaborator', true)->first();

                        //Tìm CTV F1 là CTV giới thiệu gián tiếp cho vào collaborator_by_customer_referral_id
                        if ($customer_f2 != null) {
                            $customer_f1 = Customer::where('phone_number', $customer_f2->referral_phone_number)
                                ->where(
                                    'store_id',
                                    $request->store->id
                                )->where('is_collaborator', true)->first();
                        }


                        if ($customer_f1  == null ||  $customer_f2  == null) {
                            $orderExists->update([
                                'collaborator_by_customer_referral_id' => null
                            ]);
                        } else {
                            if ($customer_f1 != null) {
                                $orderExists->update([
                                    'share_collaborator_referen' => 0,
                                    'collaborator_by_customer_referral_id' => $customer_f1->id
                                ]);
                            }
                        }

                        if ($orderExists->collaborator_by_customer_referral_id  != null) {
                            //Cộng tiền chia sẻ cho ctv t2
                            if ($configExists->bonus_type_for_ctv_t2 == 1) {
                                $share_collaborator = $orderExists->share_collaborator * ($configExists->percent_collaborator_t1 / 100);
                                if ($customer_f1->id == $orderExists->collaborator_by_customer_id) {
                                    $orderExists->update([
                                        'share_collaborator_referen' => $share_collaborator
                                    ]);
                                } else {
                                    $orderExists->update([
                                        'share_collaborator_referen' => $share_collaborator
                                    ]);
                                }
                            } else {
                                $share_collaborator_referen = ($orderExists->total_after_discount + $orderExists->bonus_points_amount_used + $orderExists->balance_collaborator_used) * ($configExists->percent_collaborator_t1 / 100);
                                $orderExists->update([
                                    'share_collaborator_referen' =>  $share_collaborator_referen,
                                ]);
                            }
                        }
                    }
                }
            }


            $configExists = AgencyConfig::where(
                'store_id',
                $request->store->id
            )->first();
            //Xử lý cho đại lý
            if ($configExists != null) {

                //***Tính hoa hồng trực tiếp

                //TH1 Đại lý bấm đặt hộ  và share link
                //trước tiên kiểm tra đơn hàng có xuất phát từ id customer đã làm ctv ko (phải thì sét lại id ctv cho đơn)

                //TH2 tìm người giới thiệu qua khách mua qua sdt (ưu tiên qua sdt)
                $customer_mh = Customer::where('id', $orderExists->customer_id)->where(
                    'store_id',
                    $request->store->id
                )->where('is_collaborator', false)->where('is_agency', false)->first();


                if ($customer_mh != null) {
                    //Tìm ctv t2 (là CTV giới thiệu CTV chính của đơn)
                    $customer_gt = Customer::where('phone_number', $customer_mh->referral_phone_number)
                        ->where(
                            'store_id',
                            $request->store->id
                        )->where('is_agency', true)->first();


                    if ($customer_gt != null) {
                        $orderExists->update([
                            'agency_ctv_by_customer_id' => $customer_gt->id,
                            'agency_ctv_by_customer_referral_id' => null,
                        ]);
                    }
                }



                if ($orderExists->agency_ctv_by_customer_id  == $orderExists->customer_id && $orderExists->is_order_for_customer == false) {
                    $orderExists->update([
                        'agency_ctv_by_customer_id' => null,
                        'share_agency' => 0
                    ]);
                }

                if ($orderExists->agency_ctv_by_customer_referral_id  == $orderExists->agency_ctv_by_customer_id) {
                    $orderExists->update([
                        'agency_ctv_by_customer_referral_id' => null,
                        'share_agency_referen' => 0
                    ]);
                }

                //Đã có ctv thì khỏi đại lý hoa hồng
                if ($orderExists->collaborator_by_customer_id  != null) {
                    $orderExists->update([
                        'agency_ctv_by_customer_id' => null,
                        'share_agency' => 0
                    ]);
                }


                if (
                    $orderExists->agency_ctv_by_customer_referral_id != null && $orderExists->agency_ctv_by_customer_id != null &&
                    AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id) == true &&
                    AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_id) == true
                ) {
                    $orderExists->update([
                        'agency_ctv_by_customer_referral_id' => null,
                        'share_agency_referen' => 0
                    ]);
                }


                if (
                    $orderExists->agency_ctv_by_customer_referral_id != null && $orderExists->agency_ctv_by_customer_id == null &&
                    AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id) == true
                ) {
                    $share_agency = 0;
                    $agency = AgencyUtils::getAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id);

                    foreach ($allCart as $lineItem) {
                        if ($lineItem->is_bonus == false) {
                            if ($agency  != null) {
                                $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                                $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                            }
                        }
                    }
                    $share_agency_referen = $share_agency * ($configExists->percent_agency_t1 / 100);

                    $orderExists->update([
                        'share_agency_referen' => $share_agency_referen
                    ]);
                };


                //Xử lý đại lý giới thiệu gián tiếp
                $configAgencyExists = AgencyConfig::where(
                    'store_id',
                    $request->store->id
                )->first();
                //***Tính hoa hồng gián tiếp cho Đại lý gt CTV
                if ($configAgencyExists != null && $configAgencyExists->percent_agency_t1 > 0) {
                    //Tìm ctv f2 đã mua hàng
                    $customer_ctv_f2 = Customer::where('id', $orderExists->collaborator_by_customer_id)->where(
                        'store_id',
                        $request->store->id
                    )->first();

                    //xác nhận người giới thiệu lại đại lý và giới thiệu cho ctv mua hàng
                    if ($customer_ctv_f2  != null &&  $customer_ctv_f2->referral_phone_number && CollaboratorUtils::isCollaborator($customer_ctv_f2->id, $request->store->id)) {
                        //Tìm đại lý t1 (là Đại lý giới thiệu CTV mua đơn này)
                        $customer_agancy_f1 = Customer::where('phone_number',   $customer_ctv_f2->referral_phone_number)
                            ->where(
                                'store_id',
                                $request->store->id
                            )->where('is_agency', true)->first();


                        if ($orderExists->agency_ctv_by_customer_referral_id == $orderExists->agency_ctv_by_customer_id) {
                            $orderExists->update([
                                'share_agency_referen' => 0,
                                'agency_ctv_by_customer_referral_id' => null
                            ]);
                        }


                        if ($customer_agancy_f1 != null) {

                            $orderExists->update([
                                'agency_ctv_by_customer_referral_id' => $customer_agancy_f1->id
                            ]);

                            if (AgencyUtils::getAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id)) {
                                //Cộng tiền chia sẻ cho ctv t2
                                if ($configAgencyExists->bonus_type_for_ctv_t2 == 1) {

                                    /////////Tính chia sẻ cho Agency
                                    $allCart =  $orderExists->line_items;
                                    $share_agency  = 0;

                                    foreach ($allCart as $lineItem) {

                                        if ($lineItem->is_bonus == false) {
                                            $agency = AgencyUtils::getAgencyByCustomerId($customer_agancy_f1->id);
                                            if ($agency  != null) {
                                                $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                                                $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                                            }
                                        }
                                    }

                                    $share_agency = $share_agency * ($configAgencyExists->percent_agency_t1 / 100);

                                    $orderExists->update([
                                        'share_agency_referen' => $share_agency,
                                    ]);
                                } else {
                                    $share_agency = ($orderExists->total_after_discount + $orderExists->bonus_points_amount_used + $orderExists->balance_agency_used + $orderExists->balance_collaborator_used) * ($configAgencyExists->percent_agency_t1 / 100);
                                    $orderExists->update([
                                        'share_agency_referen' => $share_agency,
                                        'agency_ctv_by_customer_referral_id' => $customer_agancy_f1->id
                                    ]);
                                }
                            }
                        }
                    }
                }


                //Cộng tiền chia sẻ cho đại lý chính 
                if (
                    $orderExists->agency_ctv_by_customer_id != null && $orderExists->agency_by_customer_id == null &&
                    CollaboratorUtils::isCollaborator($orderExists->customer_id, $request->store->id) == false &&
                    AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_id) == true
                ) {


                    /////////Tính chia sẻ cho Agency
                    $allCart =  $orderExists->line_items;
                    $share_agency  = 0;

                    $agency = AgencyUtils::getAgencyByCustomerId($orderExists->agency_ctv_by_customer_id);
                    foreach ($allCart as $lineItem) {
                        if ($lineItem->is_bonus == false) {
                            if ($agency  != null) {
                                $percent_agency = ProductUtils::get_percent_agency_with_agency_type($lineItem->product->id, $agency->agency_type_id);
                                $share_agency = $share_agency + (($lineItem->item_price * ($percent_agency / 100)) * $lineItem->quantity);
                            }
                        }
                    }

                    $orderExists->update([
                        'share_agency' =>  $share_agency
                    ]);
                }
            }


            //Cộng tiền hoa hồng cho đại lý đặt hộ
            if ($request->customer != null && $orderExists->is_order_for_customer && AgencyUtils::isAgencyByCustomerId($request->customer->id)) {
                if ($orderExists->total_commission_order_for_customer > 0) {
                    $orderExists->update([
                        'share_agency' =>  $orderExists->share_agency,
                        'agency_ctv_by_customer_id' =>  $request->customer->id
                    ]);
                }
            }

            //Nếu là đại lý đặt hàng mà không phải đặt hộ thì không có gt trực tiếp, gián tiếp
            if ($orderExists->agency_by_customer_id) {
                $orderExists->update([
                    'share_collaborator' => 0,
                    'share_collaborator_referen' => 0,
                    'collaborator_by_customer_id' => null,
                    'collaborator_by_customer_referral_id' => null,
                    'share_agency' => 0,
                    'agency_ctv_by_customer_id' => null,
                    'share_agency_referen' => 0,
                    'agency_ctv_by_customer_referral_id' => null,
                ]);
            }
        }


        //Xử lý gửi tin otp nếu shop bật cho phép gửi đơn hàng
        if ($request->store != null) {
            HistorySmsService::sendOrderSms($request->store->id, $orderCreated);
        }


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Order::where('id', $orderCreated->id)->first()
        ], 201);
    }


    /**
     * Danh sách Order
     * Trạng thái đơn hàng saha
     * - Chờ xử lý (WAITING_FOR_PROGRESSING)
     * - Đang chuẩn bị hàng (PACKING)
     * - Hết hàng (OUT_OF_STOCK)
     * - Shop huỷ (USER_CANCELLED)
     * - Khách đã hủy (CUSTOMER_CANCELLED)
     * - Đang giao hàng (SHIPPING)
     * - Lỗi giao hàng (DELIVERY_ERROR)
     * - Đã hoàn thành (COMPLETED)
     * - Chờ trả hàng (CUSTOMER_RETURNING)
     * - Đã trả hàng (CUSTOMER_HAS_RETURNS)
     * ############################################################################
     * Trạng thái thanh toán
     * - Chưa thanh toán (UNPAID)
     * - Chờ xử lý (WAITING_FOR_PROGRESSING)
     * - Đã thanh toán (PAID)
     * - Đã thanh toán một phần (PARTIALLY_PAID)
     * - Đã hủy (CANCELLED)
     * - Đã hoàn tiền (REFUNDS)
     * @urlParam  store_code required Store code. Example: kds
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  field_by Chọn trường nào để lấy
     * @queryParam  field_by_value Giá trị trường đó
     * @queryParam  date_from Từ thời gian nào
     * @queryParam  date_to Đến thời gian nào. Example: 4
     * @queryParam  phone_number Số điện thoại người đặt hàng
     * 
     */
    public function getAll(Request $request, $id)
    {

        $is_collaborator = str_contains($request->url(), "collaborator/");
        $is_agency = str_contains($request->url(), "agency/");
        $is_agency_ctv = str_contains($request->url(), "agency_ctv/");

        $orderModel = null;

        if (request('phone_number') != null) {

            $orderModel = Order::where(
                'orders.store_id',
                $request->store->id
            )
                ->where('orders.phone_number', '=', request('phone_number'));
            if ($is_collaborator) {

                $orderModel->where(function ($query) use ($request) {
                    $query->where('orders.collaborator_by_customer_id', '=', $request->customer->id)
                        ->orWhere('orders.collaborator_by_customer_referral_id', '=', $request->customer->id);
                });
            }

            if ($is_agency_ctv) {

                $orderModel->where(function ($query) use ($request) {
                    $query->where('orders.agency_ctv_by_customer_id', '=', $request->customer->id)
                        ->orWhere('orders.agency_ctv_by_customer_referral_id', '=', $request->customer->id);
                })->where(function ($query) use ($request) {
                    $query->where('orders.share_agency', '>', 0)
                        ->orWhere('orders.share_agency_referen', '>', 0);
                });
            }
        } else if ($is_collaborator == true) {
            $orderModel = Order::where('store_id', $request->store->id)
                ->where(function ($query) use ($request) {

                    $query->where('orders.collaborator_by_customer_id', '=', $request->customer->id)
                        ->orWhere('orders.collaborator_by_customer_referral_id', '=', $request->customer->id)

                        ->orWhere(function ($query) use ($request) {
                            $query->when(request('order') !== 'import', function ($q) use ($request) {
                                $q->where('collaborator_by_customer_referral_id', $request->customer->id);
                            });
                        });
                });



            // $orderModel =   Order::where(
            //     'orders.store_id',
            //     $request->store->id
            // )->where(function ($query) use ($request) {

            //     $query->where(
            //         'collaborator_by_customer_id',
            //         $request->customer->id
            //     )
            //         ->orWhere('collaborator_by_customer_referral_id', '=', $request->customer->id);
            // });
        } else
        if ($is_agency == true) {
            $orderModel =   Order::where(
                'orders.store_id',
                $request->store->id
            )
                ->where(
                    'agency_by_customer_id',
                    $request->customer->id
                );
        } else
        if ($is_agency_ctv == true) {
            $orderModel =   Order::where(
                'orders.store_id',
                $request->store->id
            )
                ->where(function ($query) use ($request) {
                    $query->where('agency_ctv_by_customer_id', $request->customer->id)
                        ->orWhere('agency_ctv_by_customer_referral_id', '=', $request->customer->id);
                })->where(function ($query) use ($request) {
                    $query->where('orders.share_agency', '>', 0)
                        ->orWhere('orders.share_agency_referen', '>', 0);
                });
        } else {
            $orderModel = Order::where(
                'orders.store_id',
                $request->store->id
            )
                ->where(
                    'orders.customer_id',
                    $request->customer->id
                );
        }


        $total_data = [];

        foreach (StatusDefineCode::defineDataOrder() as $statusOrder) {
            $r =  clone $orderModel;
            $key = $statusOrder[1];
            $va = $r
                ->where('order_status', $statusOrder[0])->count();
            $total_data[$key] = $va;
        }

        foreach (StatusDefineCode::defineDataPayment() as $statusOrder) {
            $r =  clone $orderModel;
            $key = $statusOrder[1];
            $va = $r
                ->where('payment_status', $statusOrder[0])->count();
            $total_data[$key] = $va;
        }

        $search = request('search');
        $descending = filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $field_by = request('field_by') ?: null;
        $field_by_value = request('field_by_value') ?: null;

        if ($field_by == "order_status_code") {
            $field_by = "order_status";
            $field_by_value  = StatusDefineCode::getOrderStatusNum($field_by_value);
        }

        if ($field_by == "payment_status_code") {
            $field_by = "payment_status";
            $field_by_value  = StatusDefineCode::getPaymentStatusNum($field_by_value);
        }

        // $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        // $dateFromDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day;
        // $dateToDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day;

        $date_from = request('date_from');
        $date_to = request('date_to');

        $orders =  $orderModel
            ->when(Schema::hasColumn('orders', $sortColumn = request('sort_by')), function ($query) use ($sortColumn, $descending) {
                $query->orderBy($sortColumn, $descending);
            })
            ->when(request('sort_by') == null, function ($query) {
                $query->orderBy('orders.created_at', 'desc');
            })
            ->orderBy('orders.created_at', 'desc')
            ->when($field_by !== null && $field_by !== "" && $field_by_value !== null, function ($query) use ($field_by, $field_by_value) {
                $query->where($field_by, $field_by_value);
            })
            ->when($date_from, function ($query) use ($date_from) {
                $query->whereDate('orders.created_at', '>=', $date_from);
            })
            ->when($date_to, function ($query) use ($date_to) {
                $query->whereDate('orders.created_at', '<=', $date_to);
            })
            ->search($search);

        $orders  =   $orders->paginate(20);

        $custom = collect(
            $total_data
        );
        $data = $custom->merge($orders);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $data,
        ], 200);
    }

    /**
     * Lấy thông tin 1 đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  order_code required order_code. Example: order_code
     */
    public function getOne(Request $request, $id)
    {

        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->where('customer_id', $request->customer->id)
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


        $orderExists->customer_address =  $orderExists->getCustomerAddressAttribute();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $orderExists
        ], 200);
    }


    /**
     * Hủy đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  order_code required Mã đơn hàng. Example: 1
     * @bodyParam note string required Lý do
     */
    public function cancel_order(Request $request)
    {

        $orderExists = Order::where('customer_id', $request->customer->id)
            ->where('store_id', $request->store->id)
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

        if ($orderExists->order_status == 4) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ORDER_HAS_BEEN_CANCELED_BEFORE[0],
                'msg' => MsgCode::ORDER_HAS_BEEN_CANCELED_BEFORE[1],
            ], 400);
        }

        if ($orderExists->order_status != 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PROCESSED_ORDERS_CANNOT_BE_CANCELED[0],
                'msg' => MsgCode::PROCESSED_ORDERS_CANNOT_BE_CANCELED[1],
            ], 400);
        }

        $orderExists->update(
            [
                "order_status" => StatusDefineCode::CUSTOMER_CANCELLED,
            ]
        );

        if ($request->note != null) {
            StatusDefineCode::saveOrderStatus(
                $request->store->id,
                $request->customer->id,
                $orderExists->id,
                "Bị hủy với lý do: " . $request->note,
                1,
                true,
                StatusDefineCode::CUSTOMER_CANCELLED
            );
        }


        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Shop ' . $request->store->name,
            'Đơn hàng ' . $request->order_code . ' đã bị khách hủy',
            TypeFCM::CUSTOMER_CANCELLED_ORDER,
            $request->order_code,
            null
        );

        PushNotificationStaffJob::dispatch(
            $request->store->id,
            'Shop ' . $request->store->name,
            'Đơn hàng ' . $request->order_code . ' đã bị khách hủy',
            TypeFCM::CUSTOMER_CANCELLED_ORDER,
            $request->order_code,
            null,
            null
        );

        RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
        RefundUtitls::auto_refund_point_for_customer($orderExists, $request);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Lịch sử trạng thái đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  order_id required order_id. Example: kds
     */
    public function status_records(Request $request)
    {

        $orderRecords = OrderRecord::where('customer_id', $request->customer->id)
            ->where('store_id', $request->store->id)
            ->where('order_id', $request->order_id)
            ->where('customer_cant_see', true)
            ->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $orderRecords
        ], 200);
    }


    /**
     * Thay đổi phương thức thanh toán
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  payment_method_id Id phương thức thanh toán 
     * @bodyParam  payment_partner_id Id hình thức thanh toán
     */
    public function change_payment_method(Request $request)
    {

        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->where('customer_id', $request->customer->id)
            ->first();

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        if ($request->payment_method_id === null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PAYMENT_METHOD_ID_IS_REQUIRED[0],
                'msg' => MsgCode::PAYMENT_METHOD_ID_IS_REQUIRED[1],
            ], 400);
        }

        $listMethodConfig = CustomerPaymentMethodController::get_payment_method_availible($request);

        $listMethodID = array();
        foreach ($listMethodConfig  as  $method) {
            array_push($listMethodID, $method["id"]);
        }

        if (!in_array($request->payment_partner_id, $listMethodID)) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::INVALID_PAYMENT_METHOD[0],
                'msg' => MsgCode::INVALID_PAYMENT_METHOD[1],
            ], 400);
        }

        $orderExists->update(
            [
                "payment_method_id" => $request->payment_method_id,
                "payment_partner_id" => $request->payment_partner_id,


            ]
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
