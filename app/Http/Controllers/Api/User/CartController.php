<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\InventoryUtils;
use App\Helper\Place;
use App\Helper\ProductUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\TypeFCM;
use App\Helper\VoucherUtils;
use App\Http\Controllers\Api\Customer\CustomerCartController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;

use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\CcartItem;
use App\Models\Combo;
use App\Models\Customer;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\LineItem;
use App\Models\ListCart;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\SubElementDistribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Giỏ hàng
 * 
 */
class CartController extends Controller
{


    /**
     * Lên đơn hàng và thanh toán
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @urlParam  cart_id required cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
     *  @bodyParam amount_money double số tiền thanh toán  (truyền lên đầy đủ sẽ tự động thanh toán, truyền 1 phần tự động chuyển thanh trạng thái thanh toán 1 phần, truyền 0 chưa thanh toán)
     *  @bodyParam payment_method int phương thức thanh toán
     * 
     * 
     */
    public function order_pay(Request $request)
    {
        $cartCustomer = new CustomerOrderController();
        return $cartCustomer->create($request);
    }


    /**
     * Danh sách giỏ hàng
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @queryParam  has_cart_default required có hiển thị đơn mặc định hay không
     * 
     * 
     */
    public function cart_list(Request $request)
    {

        $has_cart_default = filter_var(request("has_cart_default"), FILTER_VALIDATE_BOOLEAN);

        $listCart = ListCart::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)


            ->when($request->user != null, function ($query) use ($request) {
                $query->where(
                    'user_id',
                    $request->user->id
                );
            })
            ->when($request->staff != null, function ($query) use ($request) {
                $query->where(
                    'staff_id',
                    $request->staff->id
                );
            })
            ->where('edit_order_code', null)

            ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listCart,
        ], 200);
    }


    /**
     * Tạo giỏ hàng chỉnh sửa
     * 
     * @urlParam  store_code required Store code
     * 
     * @bodyParam edit_order_code string Mã hóa đơn cần chỉnh sửa
     * 
     */
    public function create_cart_edit_order(Request $request)
    {

        $edit_order_code = $request->route()->parameter('order_code');

        $orderExists = Order::where('order_code', $edit_order_code)->where('store_id', $request->store->id)
            ->first();

        if (empty($edit_order_code) || $orderExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 400);
        }

        $name = $edit_order_code;
        //Xóa item giỏ cũ theo tên
        $listCartIdHas = ListCart::where('store_id', $request->store->id)->where('name', $name)->pluck('id')->toArray();
        CcartItem::where('store_id', $request->store->id)->whereIn('list_cart_id',  $listCartIdHas)->delete();

        //Xóa item giỏ cũ theo order_code
        $listCartIdHas = ListCart::where('store_id', $request->store->id)->where('edit_order_code', $edit_order_code)->pluck('id')->toArray();
        CcartItem::where('store_id', $request->store->id)->whereIn('list_cart_id',  $listCartIdHas)->delete();

        ListCart::where('store_id', $request->store->id)->where('name', $name)->delete();
        ListCart::where('store_id', $request->store->id)->where('edit_order_code', $edit_order_code)->delete();

        $cartCreate = ListCart::create(
            [
                'edit_order_code' =>  $edit_order_code,
                'name' =>  $name,
                'store_id' => $request->store->id,
                'branch_id' =>   $orderExists->branch_id,
                'staff_id' => $request->staff == null ? null : $request->staff->id,
                'user_id' => $request->user == null ? null : $request->user->id,
                'customer_id' =>  $orderExists->customer_id,

                'points_amount_used_edit_order' => $orderExists->bonus_points_amount_used,
                'points_total_used_edit_order' => $orderExists->total_points_used,

                'balance_collaborator_used_before' => $orderExists->balance_collaborator_used,
                'balance_agency_used_before' => $orderExists->balance_agency_used,
                'is_use_balance_collaborator' => $orderExists->is_use_balance_collaborator,
                'partner_shipper_id' => $orderExists->partner_shipper_id,
                'shipper_type' =>  $orderExists->shipper_type,
                'total_shipping_fee' =>  $orderExists->total_shipping_fee,
                'ship_discount_amount' =>  $orderExists->ship_discount_amount,
                'discount' =>  $orderExists->discount,
                'customer_address_id' =>  $orderExists->customer_address_id,
                'customer_note' =>  $orderExists->customer_note,
                'customer_phone' =>  $orderExists->customer_phone,
                'customer_name' =>  $orderExists->customer_name,
                'customer_email' =>  $orderExists->customer_email,
                'customer_sex' =>  $orderExists->customer_sex,
                'customer_date_of_birth' =>  $orderExists->customer_date_of_birth,
                'payment_method_id' =>  $orderExists->payment_method_id,

                'address_detail' =>  $orderExists->customer_address_detail,
                'province' =>  $orderExists->customer_province,
                'district' =>  $orderExists->customer_district,
                'wards' =>  $orderExists->customer_wards,
            ]
        );

        $lineItemsOrders = LineItem::where('store_id', $request->store->id)
            ->where('order_id',   $orderExists->id)->get();


        foreach ($lineItemsOrders  as   $lineItem) {
            if ($lineItem->is_bonus) continue;
            $lineItem = CcartItem::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $lineItem->customer_id,
                    'product_id' => $lineItem->product_id,
                    'element_distribute_id' => $lineItem->element_distribute_id,
                    'sub_element_distribute_id' => $lineItem->sub_element_distribute_id,
                    'quantity' => $lineItem->quantity,
                    'note' => $lineItem->note,
                    'distributes' => $lineItem->distributes,
                    'user_id' => $lineItem->user_id,
                    'staff_id' => $lineItem->staff_id,
                    'list_cart_id' => $cartCreate->id,
                    'has_edit_item_price' =>  true,
                    'before_discount_price' =>  $lineItem->before_discount_price,
                    'item_price' =>  $lineItem->item_price,
                    'is_bonus' =>  $lineItem->is_bonus,
                    'allows_choose_distribute' => $lineItem->allows_choose_distribute,
                ]
            );
        }



        return $this->getOneCart($request);
    }

    /**
     * Tạo giỏ hàng mới
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @bodyParam name string Tên giỏ hàng (Không truyền sẽ tự động đặt lên Hóa đơn x)
     * 
     */
    public function create_cart(Request $request)
    {

        $name = $request->name;
        if (empty($name)) {
            $listCartName = ListCart::where('store_id', $request->store->id)
                ->where('branch_id', $request->branch->id)
                ->where('edit_order_code', null)
                ->orderBy('created_at', 'desc')->pluck('name')->toArray();


            $nextNum = 1;

            if (count($listCartName) > 0) {
                $listNum = [];
                foreach ($listCartName as $cartName) {
                    $new_str = str_replace('Hóa đơn', '', $cartName);
                    $n =  intval($new_str);

                    array_push($listNum, $n);
                }

                while (in_array($nextNum, $listNum)) {
                    $nextNum = $nextNum + 1;
                }
            }

            $name = "Hóa đơn " . $nextNum;
        }

        $cartCreate = ListCart::create(
            [
                'name' =>  $name,
                'store_id' => $request->store->id,
                'branch_id' => $request->branch->id,
                'staff_id' => $request->staff == null ? null : $request->staff->id,
                'user_id' => $request->user == null ? null : $request->user->id,
            ]
        );

        $listCart = ListCart::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('edit_order_code')
            ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $listCart
        ], 201);
    }

    /**
     * Tạo giỏ hàng mới
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @bodyParam name string Tên giỏ hàng
     * 
     */
    public function create_cart_save(Request $request)
    {

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $oneCart = $this->get_one_cart_default($request);


        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }
        $allCart = CustomerCartController::all_items_cart($request, $oneCart->id);

        if (count($allCart) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NOTHING_TO_COPY[0],
                'msg' => MsgCode::NOTHING_TO_COPY[1],
            ], 400);
        }

        $oneCartNew = $oneCart->replicate();
        $oneCartNew->name  = $request->name;
        $oneCartNew->is_default = false;
        $oneCartNew->save();

        foreach ($allCart  as $lineItem) {
            $lineItemNew = $lineItem->replicate();
            $lineItemNew->list_cart_id  =  $oneCartNew->id;
            $lineItemNew->save();
        }


        $listCart = ListCart::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('edit_order_code', null)
            ->orderBy('created_at', 'desc')->get();

        CcartItem::where('list_cart_id', $oneCart->id)
            ->where('store_id', $request->store->id)
            ->delete();


        $oneCart->update([
            "customer_id" =>  null,
            "code_voucher" =>   "",
            "is_use_points" => false,
            "is_use_balance_collaborator" =>  false,
            "payment_method_id" =>  null,
            "partner_shipper_id" =>  null,
            "shipper_type" =>  null,
            "total_shipping_fee" => 0,
            "discount" => 0,
            "customer_address_id" =>  null,
            "customer_note" => "",

            "customer_phone" => "",
            "customer_name" => "",
            "customer_id" => null,

            "address_detail" => "",

            "province" => null,
            "district" => null,
            "wards" =>  null,
        ]);




        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $listCart
        ], 201);
    }

    /**
     * Cập nhật thông tin giỏ hàng
     * 
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @bodyParam name string Tên giỏ hàng
     * @bodyParam is_use_points boolean có sử dụng điểm thưởng hay không
     * @bodyParam is_use_balance_collaborator boolean su dung diem CTV
     * @bodyParam payment_method_id integer ID phương thức thanh toán
     * @bodyParam partner_shipper_id integer required ID nhà giao hàng
     * @bodyParam shipper_type integer required (partner_shipper_id != null) Kiểu giao (0 tiêu chuẩn - 1 siêu tốc)
     * @bodyParam total_shipping_fee integer required (partner_shipper_id != null) Tổng tiền giao hàng
     * @bodyParam collaborator_by_customer_id int customer  ID CTV
     * @bodyParam agency_by_customer_id int ID customer Đại lý
     * @bodyParam customer_phone string Số điện thoại customer
     * @bodyParam customer_name string Tên khách hàng
     * @bodyParam customer_address_id integer required ID địa chỉ khách hàng
     * @bodyParam customer_note string required Ghi chú khách hàng
     * @bodyParam customer_email string required Email
     * @bodyParam customer_sex string required  giới tính
     * @bodyParam customer_date_of_birth string required Ngày sinh
     * 
     * 
     * 
     */
    public function update_cart(Request $request)
    {
        $cart_id = $request->route()->parameter('cart_id');

        $listCart = $this->get_one_cart_default($request);

        if ($listCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        $ship_discount_amount = $listCart->ship_discount_amount;
        $total_shipping_fee = $request->total_shipping_fee - $ship_discount_amount > 0 ? $request->total_shipping_fee - $ship_discount_amount  : 0;


        $listCart->update(
            [
                'customer_id' => $request->customer_id,
                'name' => $request->name ?? $listCart->name,

                'is_use_points' => $request->is_use_points === null ?   $listCart->is_use_points :  $request->is_use_points,
                'is_use_balance_collaborator' => $request->is_use_balance_collaborator,
                'partner_shipper_id' => $request->partner_shipper_id ?? $listCart->partner_shipper_id,
                'shipper_type' =>  $request->shipper_type ?? $listCart->shipper_type,
                'total_shipping_fee' =>  $total_shipping_fee,
                'discount' =>  $request->discount,
                'customer_address_id' =>  $request->customer_address_id,
                'customer_note' =>  $request->customer_note,
                'customer_phone' =>  $request->customer_phone,
                'customer_name' =>  $request->customer_name,
                'customer_email' =>  $request->customer_email,
                'customer_sex' =>  $request->customer_sex,
                'customer_date_of_birth' =>  $request->customer_date_of_birth,

                'payment_method_id' =>  $request->payment_method_id,

                'address_detail' =>  $request->address_detail,
                'province' =>  $request->province,
                'district' =>  $request->district,
                'wards' =>  $request->wards,
            ]
        );

        return $this->getOneCart($request);
    }

    public function update_cartV1(Request $request)
    {
        $cart_id = $request->route()->parameter('cart_id');

        $listCart = $this->get_one_cart_default($request);

        if ($listCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        $ship_discount_amount = $listCart->ship_discount_amount;
        $total_shipping_fee = $request->total_shipping_fee - $ship_discount_amount > 0 ? $request->total_shipping_fee - $ship_discount_amount  : 0;


        $listCart->update(
            [
                'customer_id' => $request->customer_id,
                'name' => $request->name ?? $listCart->name,

                'is_use_points' => $request->is_use_points === null ?   $listCart->is_use_points :  $request->is_use_points,
                'is_use_balance_collaborator' => $request->is_use_balance_collaborator,
                'partner_shipper_id' => $request->partner_shipper_id ?? $listCart->partner_shipper_id,
                'shipper_type' =>  $request->shipper_type ?? $listCart->shipper_type,
                'total_shipping_fee' =>  $total_shipping_fee,
                'discount' =>  $request->discount,
                'customer_address_id' =>  $request->customer_address_id,
                'customer_note' =>  $request->customer_note,
                'customer_phone' =>  $request->customer_phone,
                'customer_name' =>  $request->customer_name,
                'customer_email' =>  $request->customer_email,
                'customer_sex' =>  $request->customer_sex,
                'customer_date_of_birth' =>  $request->customer_date_of_birth,

                'payment_method_id' =>  $request->payment_method_id,

                'address_detail' =>  $request->address_detail,
                'province' =>  $request->province,
                'district' =>  $request->district,
                'wards' =>  $request->wards,
            ]
        );

        return $this->getOneCartV1($request);
    }

    public function update_name_cart(Request $request)
    {
        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        $oneCart->update(
            [
                'name' => $request->name ?? $oneCart->name,
            ]
        );

        return $this->getOneCart($request);
    }

    /**
     * Sử dụng voucher
     * 
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @bodyParam name string Tên giỏ hàng
     * @bodyParam code_voucher string ":"SUPER" gửi code voucher (không xài thì truyền voucher)
     * @urlParam  cart_id required cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
     * 
     */
    public function use_voucher(Request $request)
    {


        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        if (empty($request->code_voucher)) {

            $oneCart->update(
                [
                    'code_voucher' => '',

                ]
            );


            return $this->getOneCart($request);
        }





        $allCart = CustomerCartController::all_items_cart($request, $oneCart->id);

        $response_info_cart =  CustomerCartController::data_response(
            $allCart,
            $request,
            $oneCart
        );



        $check_voucher =   VoucherUtils::data_voucher_discount_for_0(
            $request->code_voucher,
            $allCart,
            $request,
            $response_info_cart['data']['total_after_discount']
        );

        if (isset($check_voucher['msg_code'])) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => $check_voucher['msg_code'][0],
                'msg' => $check_voucher['msg_code'][1],
            ], 400);
        }


        $oneCart->update(
            [
                'code_voucher' => $request->code_voucher,
                // 'total_shipping_fee' => $response_info_cart['data']['total_shipping_fee']
            ]
        );


        return $this->getOneCart($request);
    }

    public function use_voucherV1(Request $request)
    {


        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        if (empty($request->code_voucher)) {

            $oneCart->update(
                [
                    'code_voucher' => '',

                ]
            );


            return $this->getOneCartV1($request);
        }





        $allCart = CustomerCartController::all_items_cart($request, $oneCart->id);

        $response_info_cart =  CustomerCartController::data_responseV1(
            $allCart,
            $request,
            $oneCart
        );



        $check_voucher =   VoucherUtils::data_voucher_discount_for_0(
            $request->code_voucher,
            $allCart,
            $request,
            $response_info_cart['data']['total_after_discount']
        );

        if (isset($check_voucher['msg_code'])) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => $check_voucher['msg_code'][0],
                'msg' => $check_voucher['msg_code'][1],
            ], 400);
        }


        $oneCart->update(
            [
                'code_voucher' => $request->code_voucher,
                // 'total_shipping_fee' => $response_info_cart['data']['total_shipping_fee']
            ]
        );


        return $this->getOneCartV1($request);
    }

    function auto_update_code_voucher($oneCart, $request)
    {


        if (!empty($oneCart->code_voucher)) {

            $allCart = CustomerCartController::all_items_cart($request, $oneCart->id);

            $response_info_cart =  CustomerCartController::data_response(
                $allCart,
                $request,
                $oneCart
            );


            $check_voucher =   VoucherUtils::data_voucher_discount_for_0(
                $request->code_voucher,
                $allCart,
                $request,
                $response_info_cart['data']['total_after_discount']
            );

            if (isset($check_voucher['msg_code'])) {
                $oneCart->update(
                    [
                        'code_voucher' => "",
                    ]
                );
            } else {
                $oneCart->update(
                    [
                        // 'total_shipping_fee' => $response_info_cart['data']['total_shipping_fee']
                    ]
                );
            }
        }
    }

    /**
     * Xóa 1 giỏ hàng
     * 
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @urlParam  cart_id required cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
     * 
     */
    public function delete_cart(Request $request)
    {
        $cart_id = $request->route()->parameter('cart_id');

        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        if ($oneCart->is_default == true) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::CANNOT_DELETE_DEFAULT_CART[0],
                'msg' => MsgCode::CANNOT_DELETE_DEFAULT_CART[1],
            ], 400);
        }


        $oneCart->delete();

        CcartItem::where('list_cart_id', $oneCart->id)
            ->where('store_id', $request->store->id)
            ->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' =>  $cart_id],
        ], 200);
    }

    static function get_one_cart_default($request)
    {

        $order_code = $request->route()->parameter('order_code');


        if (!empty($order_code)) {

            return ListCart::where('store_id', $request->store->id)
                ->where('edit_order_code',  $order_code)
                ->first();
        }

        $cart_id = $request->route()->parameter('cart_id');

        if ($cart_id  == 0) {

            $oneCart = ListCart::where('store_id', $request->store->id)
                ->where('branch_id', $request->branch->id)
                ->when($request->user != null, function ($query) use ($request) {
                    $query->where(
                        'user_id',
                        $request->user->id
                    );
                })
                ->when($request->staff != null, function ($query) use ($request) {
                    $query->where(
                        'staff_id',
                        $request->staff->id
                    );
                })->orderBy('id', 'asc')
                ->first();

            if ($oneCart == null) {
                $oneCart = ListCart::create(
                    [
                        'name' => $request->name,
                        'store_id' => $request->store->id,
                        'branch_id' => $request->branch->id,
                        'staff_id' => $request->staff == null ? null : $request->staff->id,
                        'user_id' => $request->user == null ? null : $request->user->id,
                        'is_default' => true
                    ]
                );
            }
        } else {
            $oneCart = ListCart::where('store_id', $request->store->id)
                ->where('branch_id', $request->branch->id)
                ->when($request->user != null, function ($query) use ($request) {
                    $query->where(
                        'user_id',
                        $request->user->id
                    );
                })
                ->when($request->staff != null, function ($query) use ($request) {
                    $query->where(
                        'staff_id',
                        $request->staff->id
                    );
                })

                ->where('id',  $cart_id)

                ->first();
        }

        return $oneCart;
    }

    /**
     * Danh sách sản phẩm trong giỏ hàng
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @urlParam  cart_id required cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
     */
    public function getOneCart(Request $request)
    {
        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        CustomerCartController::handle_bonus_product($request, $oneCart->id);

        $allCart = CustomerCartController::all_items_cart($request, $oneCart->id);

        if (!empty($oneCart->code_voucher)) {

            $response_info_cart =  CustomerCartController::data_response(
                $allCart,
                $request,
                $oneCart
            );

            $check_voucher =   VoucherUtils::data_voucher_discount_for_0(
                $oneCart->code_voucher,
                $allCart,
                $request,
                $response_info_cart['data']['total_after_discount']
            );

            if (isset($check_voucher['msg_code'])) {
                $oneCart->update([
                    'code_voucher' => "",
                ]);
            }
        }


        $oneCart->customer = Customer::where('store_id', $request->store->id)->where('id', $oneCart->customer_id)->first();

        if ($oneCart->customer == null) {

            $oneCart->customer = Customer::where('store_id', $request->store->id)->where('phone_number', $oneCart->customer_phone)->first();
        }

        $oneCart->province_name = Place::getNameProvince($oneCart->province);
        $oneCart->district_name = Place::getNameDistrict($oneCart->district);
        $oneCart->wards_name = Place::getNameWards($oneCart->wards);

        $response_info_cart =  CustomerCartController::data_response(
            $allCart,
            $request,
            $oneCart
        );
        $oneCart->info_cart =   $response_info_cart['data'];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $oneCart
        ], 200);
    }

    public function getOneCartV1(Request $request)
    {
        $oneCart = $this->get_one_cart_default($request);
        if ($oneCart == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ], 400);
        }

        CustomerCartController::handle_bonus_product($request, $oneCart->id);

        $allCart = CustomerCartController::all_items_cart($request, $oneCart->id);

        if (!empty($oneCart->code_voucher)) {
            $response_info_cart =  CustomerCartController::data_responseV1(
                $allCart,
                $request,
                $oneCart
            );

            $check_voucher =   VoucherUtils::data_voucher_discount_for_0(
                $oneCart->code_voucher,
                $allCart,
                $request,
                $response_info_cart['data']['total_after_discount']
            );

            if (isset($check_voucher['msg_code'])) {
                $oneCart->update([
                    'code_voucher' => "",
                ]);
            }
        }


        $oneCart->customer = Customer::where('store_id', $request->store->id)->where('id', $oneCart->customer_id)->first();

        if ($oneCart->customer == null) {

            $oneCart->customer = Customer::where('store_id', $request->store->id)->where('phone_number', $oneCart->customer_phone)->first();
        }

        $oneCart->province_name = Place::getNameProvince($oneCart->province);
        $oneCart->district_name = Place::getNameDistrict($oneCart->district);
        $oneCart->wards_name = Place::getNameWards($oneCart->wards);
        $response_info_cart =  CustomerCartController::data_responseV1(
            $allCart,
            $request,
            $oneCart
        );
        dd($response_info_cart);

        $oneCart->info_cart =   $response_info_cart['data'];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $oneCart
        ], 200);
    }



    function find_distribute_auto($request, $productExists, $quantity)
    {

        $distribute_id = null;
        $element_distribute_id = null;
        $sub_element_distribute_id = null;

        $distribute_name = null;
        $element_distribute_name = null;
        $sub_element_distribute_name = null;

        if ($productExists->distributes != null && count($productExists->distributes) > 0) {

            if (
                isset($productExists->distributes[0]->element_distributes) && count($productExists->distributes[0]->element_distributes) > 0
                &&
                count($productExists->distributes[0]->element_distributes[0]->sub_element_distributes) > 0
            ) {
                foreach ($productExists->distributes[0]->element_distributes as $element_distribute2) {

                    $element_distribute = $element_distribute2;

                    if (is_array($element_distribute)) {
                        $element_distribute = json_decode(json_encode($element_distribute), FALSE);
                    }

                    foreach ($element_distribute->sub_element_distributes as $sub_element_distribute2) {

                        $sub_element_distribute = $sub_element_distribute2;

                        if (is_array($sub_element_distribute)) {
                            $sub_element_distribute = json_decode(json_encode($sub_element_distribute), FALSE);
                        }

                        $distribute_id = $sub_element_distribute->distribute_id ?? null;
                        $element_distribute_id = $sub_element_distribute->element_distribute_id ?? null;
                        $sub_element_distribute_id = $sub_element_distribute->id ?? null;

                        $distribute_name = $productExists->distributes[0]->name;
                        $element_distribute_name = $element_distribute->name;
                        $sub_element_distribute_name = $sub_element_distribute->name;

                        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
                            $request->store->id,
                            $request->branch->id,
                            $productExists->id,
                            $element_distribute_id,
                            $sub_element_distribute_id
                        );

                        if ($data_stock['stock'] >= $quantity) {
                            return [
                                'success' => true,
                                'distribute_id' => $distribute_id,
                                'element_distribute_id' => $element_distribute_id,
                                'sub_element_distribute_id' =>  $sub_element_distribute_id,
                                'element_distribute_name' => $element_distribute->name,
                                'sub_element_distribute_name' => $sub_element_distribute->name,
                                'distribute_name' => $productExists->distributes[0]->name
                            ];
                        }
                    }
                }

                return [
                    'success' => true,
                    'distribute_id' => $distribute_id,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' =>  $sub_element_distribute_id,
                    'element_distribute_name' => $element_distribute_name,
                    'sub_element_distribute_name' => $sub_element_distribute_name,
                    'distribute_name' => $distribute_name
                ];
            } else {

                foreach ($productExists->distributes[0]->element_distributes as $element_distribute2) {

                    $element_distribute = $element_distribute2;

                    if (is_array($element_distribute)) {
                        $element_distribute = json_decode(json_encode($element_distribute), FALSE);
                    }

                    $distribute_id = $element_distribute->distribute_id  ?? null;
                    $element_distribute_id = ($element_distribute->id) ?? null;

                    $element_distribute_name = ($element_distribute->name ??  $element_distribute['name']) ?? null;
                    $sub_element_distribute_name = null;

                    $distribute_name = $productExists->distributes[0]->name ?? $productExists->distributes[0]['name'];

                    $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
                        $request->store->id,
                        $request->branch->id,
                        $productExists->id,
                        $element_distribute_id,
                        null
                    );

                    if ($data_stock['stock'] >= $quantity) {
                        return [
                            'success' => true,
                            'distribute_id' => $distribute_id,
                            'element_distribute_id' => $element_distribute_id,
                            'sub_element_distribute_id' => null,

                            'element_distribute_name' => $element_distribute->name,
                            'sub_element_distribute_name' => null,

                            'distribute_name' => $productExists->distributes[0]->name
                        ];
                    }
                }
                return [
                    'success' => true,
                    'distribute_id' => $distribute_id,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => null,

                    'element_distribute_name' => $element_distribute_name,
                    'sub_element_distribute_name' => null,

                    'distribute_name' => $distribute_name
                ];
            }
        }
        return [
            'success' => false
        ];
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @urlParam  cart_id required cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
     * @bodyParam product_id int required Product id
     * @bodyParam distribute_name string Tên kiểu phân loại
     * @bodyParam element_distribute_name string  Kiểu phân loại
     * @bodyParam sub_element_distribute_name string  Phân loại con
     * 
     */
    public function addLineItem(Request $request, $id)
    {

        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart  == null) {
            if (empty($productExists)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CART_EXISTS[0],
                    'msg' => MsgCode::NO_CART_EXISTS[1],
                ], 400);
            }
        }

        $distribute_name = $request->distribute_name;
        $element_distribute_name = $request->element_distribute_name;
        $sub_element_distribute_name = $request->sub_element_distribute_name;



        $product_id = $request->product_id;
        $store_id = $request->store->id;

        $element_distribute_id = null;
        $sub_element_distribute_id = null;

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


        $type_product =    ProductUtils::check_type_distribute($productExists);

        if ($type_product == ProductUtils::HAS_SUB) {

            $error_sub = false;
            if (empty($distribute_name) && empty($element_distribute_name) && empty($sub_element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];

                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name) || empty($sub_element_distribute_name)) {
                $error_sub = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_sub = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_sub = true;
                    } else {
                        $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                            ->where('distribute_id', $distribute->id)
                            ->where('element_distribute_id', $ele_distribute->id)
                            ->where('name', $sub_element_distribute_name)
                            ->where('store_id', $store_id)->first();

                        if ($sub_ele_distribute  == null) {
                            $error_sub = true;
                        } else {
                            $element_distribute_id = $ele_distribute->id;
                            $sub_element_distribute_id =  $sub_ele_distribute->id;
                        }
                    }
                }
            }

            if ($error_sub  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product  == ProductUtils::HAS_ELE) {

            $distribute_name = $request->distribute_name;
            $element_distribute_name = $request->element_distribute_name;
            $sub_element_distribute_name = null;

            $error_ele = false;
            if (empty($distribute_name) && empty($element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];

                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name)) {
                $error_ele = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_ele = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_ele = true;
                    } else {
                        $element_distribute_id = $ele_distribute->id;
                    }
                }
            }

            if ($error_ele  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product == ProductUtils::NO_ELE_SUB) {
            $distribute_name = null;
            $element_distribute_name = null;
            $sub_element_distribute_name = null;
        }


        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
            $store_id,
            $request->branch->id,
            $product_id,
            $element_distribute_id,
            $sub_element_distribute_id
        );


        ///Xử lý số lượng tồn kho
        $max_quantity = $data_stock['stock'];


        $itemExists = null;
        //$items = CustomerCartController::all_items_cart($request, $oneCart->id);
        //Auto remove duplicate

        $list_cart_id = $oneCart == null ? null : $oneCart->id;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;


        $itemExists_arr = CcartItem::allItem($list_cart_id,  $request)
            ->where('is_bonus', false)
            ->where('product_id', $request->product_id)
            ->where('element_distribute_id', $element_distribute_id)
            ->where('sub_element_distribute_id', $sub_element_distribute_id)
            ->get();



        if (count($itemExists_arr) > 0) {
            $itemExists = $itemExists_arr[0];

            //xoa duplicate
            $index_item = 0;
            foreach ($itemExists_arr as $item) {
                if ($index_item != 0) {
                    $item->delete();
                }
                $index_item++;
            }
        }


        ////

        $next_quantity = $request->quantity;
        if (empty($itemExists)) {
            $next_quantity = $request->quantity;
        } else {
            $next_quantity = $itemExists->quantity + $request->quantity;
        }

        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];


        if ($allow_semi_negative == false && $productExists->check_inventory == true && $next_quantity  > $max_quantity) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[0],
                'msg' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[1],
            ], 400);
        }


        $distributes_add = $this->get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name);



        if (empty($itemExists)) {

            $lineItem = CcartItem::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $customer_id,
                    'product_id' => $product_id,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                    'distributes' => json_encode($distributes_add),
                    'user_id' => $user_id,
                    'staff_id' => $staff_id,
                    'list_cart_id' => $list_cart_id,
                    'allows_choose_distribute' => true
                ]
            );
        } else {

            $itemExists->update(
                [
                    'product_id' => $product_id,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'customer_id' =>  $customer_id,
                    'user_id' => $user_id,
                    'staff_id' => $staff_id,
                    'quantity' => $next_quantity ?? 1,
                    'distributes' =>  $distributes_add == null ? $itemExists->distributes :  json_encode($distributes_add),
                    'list_cart_id' => $list_cart_id,
                    'allows_choose_distribute' => true
                ]
            );
        }

        return $this->getOneCart($request);
    }


    public function addLineItemV1(Request $request, $id)
    {

        $oneCart = $this->get_one_cart_default($request);

        if ($oneCart  == null) {
            if (empty($productExists)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CART_EXISTS[0],
                    'msg' => MsgCode::NO_CART_EXISTS[1],
                ], 400);
            }
        }

        $distribute_name = $request->distribute_name;
        $element_distribute_name = $request->element_distribute_name;
        $sub_element_distribute_name = $request->sub_element_distribute_name;



        $product_id = $request->product_id;
        $store_id = $request->store->id;

        $element_distribute_id = null;
        $sub_element_distribute_id = null;

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


        $type_product =    ProductUtils::check_type_distribute($productExists);

        if ($type_product == ProductUtils::HAS_SUB) {

            $error_sub = false;
            if (empty($distribute_name) && empty($element_distribute_name) && empty($sub_element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];

                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name) || empty($sub_element_distribute_name)) {
                $error_sub = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_sub = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_sub = true;
                    } else {
                        $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                            ->where('distribute_id', $distribute->id)
                            ->where('element_distribute_id', $ele_distribute->id)
                            ->where('name', $sub_element_distribute_name)
                            ->where('store_id', $store_id)->first();

                        if ($sub_ele_distribute  == null) {
                            $error_sub = true;
                        } else {
                            $element_distribute_id = $ele_distribute->id;
                            $sub_element_distribute_id =  $sub_ele_distribute->id;
                        }
                    }
                }
            }

            if ($error_sub  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product  == ProductUtils::HAS_ELE) {

            $distribute_name = $request->distribute_name;
            $element_distribute_name = $request->element_distribute_name;
            $sub_element_distribute_name = null;

            $error_ele = false;
            if (empty($distribute_name) && empty($element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];

                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name)) {
                $error_ele = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_ele = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_ele = true;
                    } else {
                        $element_distribute_id = $ele_distribute->id;
                    }
                }
            }

            if ($error_ele  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product == ProductUtils::NO_ELE_SUB) {
            $distribute_name = null;
            $element_distribute_name = null;
            $sub_element_distribute_name = null;
        }


        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
            $store_id,
            $request->branch->id,
            $product_id,
            $element_distribute_id,
            $sub_element_distribute_id
        );


        ///Xử lý số lượng tồn kho
        $max_quantity = $data_stock['stock'];


        $itemExists = null;
        //$items = CustomerCartController::all_items_cart($request, $oneCart->id);
        //Auto remove duplicate

        $list_cart_id = $oneCart == null ? null : $oneCart->id;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;


        $itemExists_arr = CcartItem::allItem($list_cart_id,  $request)
            ->where('is_bonus', false)
            ->where('product_id', $request->product_id)
            ->where('element_distribute_id', $element_distribute_id)
            ->where('sub_element_distribute_id', $sub_element_distribute_id)
            ->get();



        if (count($itemExists_arr) > 0) {
            $itemExists = $itemExists_arr[0];

            //xoa duplicate
            $index_item = 0;
            foreach ($itemExists_arr as $item) {
                if ($index_item != 0) {
                    $item->delete();
                }
                $index_item++;
            }
        }


        ////

        $next_quantity = $request->quantity;
        if (empty($itemExists)) {
            $next_quantity = $request->quantity;
        } else {
            $next_quantity = $itemExists->quantity + $request->quantity;
        }

        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];


        if ($allow_semi_negative == false && $productExists->check_inventory == true && $next_quantity  > $max_quantity) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[0],
                'msg' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[1],
            ], 400);
        }


        $distributes_add = $this->get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name);



        if (empty($itemExists)) {

            $lineItem = CcartItem::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $customer_id,
                    'product_id' => $product_id,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                    'distributes' => json_encode($distributes_add),
                    'user_id' => $user_id,
                    'staff_id' => $staff_id,
                    'list_cart_id' => $list_cart_id,
                    'allows_choose_distribute' => true
                ]
            );
        } else {

            $itemExists->update(
                [
                    'product_id' => $product_id,
                    'element_distribute_id' => $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id,
                    'customer_id' =>  $customer_id,
                    'user_id' => $user_id,
                    'staff_id' => $staff_id,
                    'quantity' => $next_quantity ?? 1,
                    'distributes' =>  $distributes_add == null ? $itemExists->distributes :  json_encode($distributes_add),
                    'list_cart_id' => $list_cart_id,
                    'allows_choose_distribute' => true
                ]
            );
        }

        return $this->getOneCartV1($request);
    }


    /**
     * Thêm combo sản phẩm vào giỏ hàng
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @urlParam  cart_id required cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
     * @bodyParam combo_id int required Id của combo cần thêm
     * 
     */
    public function addComboToCart(Request $request, $id)
    {

        $oneCart = $this->get_one_cart_default($request);

        $id = $request->combo_id;
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
        $arr_id_rele = [];
        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];



        foreach ($checkComboExists->products_combo as $product_combo) {
            $product = $product_combo->product;
            $quantity = $product_combo->quantity;

            if ($allow_semi_negative  == false) {
                $max_product = null;
                if ($product->check_inventory == true) {

                    $type_product =    ProductUtils::check_type_distribute($product);

                    if ($type_product  == ProductUtils::NO_ELE_SUB) {
                        $max_product = $product->inventory['main_stock'];
                    }
                    if ($type_product  == ProductUtils::HAS_ELE) {
                        $element_distributes = $product->inventory['distributes'][0]->element_distributes;
                        foreach ($element_distributes  as  $element_distribute) {

                            if ($max_product == null || ($element_distribute != null &&  $max_product <  (($element_distribute->stock ?? $element_distribute['stock']) ?? 0))) {
                                $max_product =  ($element_distribute->stock ?? $element_distribute['stock']);
                                $arr_id_rele[$product->id]['distribute_name'] =   $product->inventory['distributes'][0]->name;
                                $arr_id_rele[$product->id]['element_distribute_id'] = ($element_distribute->id ?? $element_distribute['id']);
                                $arr_id_rele[$product->id]['element_distribute_name'] = ($element_distribute->name ?? $element_distribute['name']);
                            }

                            if ($max_product > $quantity) {
                                continue;
                            }
                        }
                    }

                    if ($type_product  == ProductUtils::HAS_SUB) {
                        $element_distributes = $product->inventory['distributes'][0]->element_distributes;
                        foreach ($element_distributes  as  $element_distribute) {

                            foreach ($element_distribute->sub_element_distributes  as  $sub_element_distribute) {


                                if ($max_product == null ||   $max_product < ($sub_element_distribute->stock ?? $sub_element_distribute['stock'])) {
                                    $max_product = $sub_element_distribute->stock ?? $sub_element_distribute['stock'];
                                    $arr_id_rele[$product->id]['distribute_name'] =   $product->inventory['distributes'][0]->name;
                                    $arr_id_rele[$product->id]['element_distribute_name'] = $element_distribute->stock ?? $element_distribute['name'];
                                    $arr_id_rele[$product->id]['element_distribute_id'] = $element_distribute->id ??  $element_distribute['id'];
                                    $arr_id_rele[$product->id]['sub_element_distribute_id'] = $sub_element_distribute->id ??  $sub_element_distribute['id'];
                                    $arr_id_rele[$product->id]['sub_element_distribute_name'] = $sub_element_distribute->name ?? $sub_element_distribute['name'];
                                }

                                if ($max_product > $quantity) {
                                    continue;
                                }
                            }
                        }
                    }

                    if ($max_product == null || $max_product < $quantity) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => "Sản phẩm '" . ($product->name) . "' trong combo đã hết hàng",
                        ], 400);
                    }
                } else {
                    $data_choose = ProductUtils::auto_choose_distribute($product, $arr_id_rele);
                    $arr_id_rele[$product->id] = $data_choose;
                }
            } else {
                $data_choose = ProductUtils::auto_choose_distribute($product, $arr_id_rele);
                $arr_id_rele[$product->id] = $data_choose;
            }
        }


        $list_cart_id = $oneCart == null ? null : $oneCart->id;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;



        foreach ($checkComboExists->products_combo as $product_combo) {
            $product = $product_combo->product;
            $quantity = $product_combo->quantity;


            $itemExists = null;
            //$items = CustomerCartController::all_items_cart($request, $oneCart->id);
            //Auto remove duplicate

            $product_id =   $product->id;
            $product_data_dis =  $arr_id_rele[$product->id] ?? null;
            $distribute_name =     $product_data_dis['distribute_name'] ?? null;
            $element_distribute_name =     $product_data_dis['element_distribute_name'] ?? null;
            $sub_element_distribute_name =    $product_data_dis['sub_element_distribute_name'] ?? null;
            $element_distribute_id =     $product_data_dis['element_distribute_id'] ?? null;
            $sub_element_distribute_id =    $product_data_dis['sub_element_distribute_id'] ?? null;

            $itemExists_arr = CcartItem::allItem($oneCart->id,  $request)
                ->where('is_bonus', false)
                ->where('product_id',  $product_id)
                ->where('element_distribute_id', $element_distribute_id)
                ->where('sub_element_distribute_id', $sub_element_distribute_id)
                ->get();


            if (count($itemExists_arr) > 0) {
                $itemExists = $itemExists_arr[0];

                //xoa duplicate
                $index_item = 0;
                foreach ($itemExists_arr as $item) {
                    if ($index_item != 0) {
                        $item->delete();
                    }
                    $index_item++;
                }
            }

            ////    ////  //// ////  //// ////  //// ////
            $next_quantity = $quantity;
            if (empty($itemExists)) {
                $next_quantity = $quantity;
            } else {
                $next_quantity = $itemExists->quantity + $quantity;
            }


            $distributes_add = $this->get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name);



            if (empty($itemExists)) {

                $lineItem = CcartItem::create(
                    [
                        'store_id' => $request->store->id,
                        'customer_id' =>  $customer_id,
                        'product_id' => $product_id,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id,
                        'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                        'distributes' => json_encode($distributes_add),
                        'user_id' => $user_id,
                        'staff_id' => $staff_id,
                        'list_cart_id' => $list_cart_id,
                        'allows_choose_distribute' => true
                    ]
                );
            } else {

                $itemExists->update(
                    [
                        'product_id' => $product_id,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id,
                        'customer_id' =>  $customer_id,
                        'user_id' => $user_id,
                        'staff_id' => $staff_id,

                        'quantity' => $next_quantity ?? 1,
                        'distributes' =>  $distributes_add == null ? $itemExists->distributes :  json_encode($distributes_add),
                        'list_cart_id' => $list_cart_id,
                        'allows_choose_distribute' => true
                    ]
                );
            }
        }

        return $this->getOneCart($request);
    }

    public function addComboToCartV1(Request $request, $id)
    {

        $oneCart = $this->get_one_cart_default($request);

        $id = $request->combo_id;
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
        $arr_id_rele = [];
        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];



        foreach ($checkComboExists->products_combo as $product_combo) {
            $product = $product_combo->product;
            $quantity = $product_combo->quantity;

            if ($allow_semi_negative  == false) {
                $max_product = null;
                if ($product->check_inventory == true) {

                    $type_product =    ProductUtils::check_type_distribute($product);

                    if ($type_product  == ProductUtils::NO_ELE_SUB) {
                        $max_product = $product->inventory['main_stock'];
                    }
                    if ($type_product  == ProductUtils::HAS_ELE) {
                        $element_distributes = $product->inventory['distributes'][0]->element_distributes;
                        foreach ($element_distributes  as  $element_distribute) {

                            if ($max_product == null || ($element_distribute != null &&  $max_product <  (($element_distribute->stock ?? $element_distribute['stock']) ?? 0))) {
                                $max_product =  ($element_distribute->stock ?? $element_distribute['stock']);
                                $arr_id_rele[$product->id]['distribute_name'] =   $product->inventory['distributes'][0]->name;
                                $arr_id_rele[$product->id]['element_distribute_id'] = ($element_distribute->id ?? $element_distribute['id']);
                                $arr_id_rele[$product->id]['element_distribute_name'] = ($element_distribute->name ?? $element_distribute['name']);
                            }

                            if ($max_product > $quantity) {
                                continue;
                            }
                        }
                    }

                    if ($type_product  == ProductUtils::HAS_SUB) {
                        $element_distributes = $product->inventory['distributes'][0]->element_distributes;
                        foreach ($element_distributes  as  $element_distribute) {

                            foreach ($element_distribute->sub_element_distributes  as  $sub_element_distribute) {


                                if ($max_product == null ||   $max_product < ($sub_element_distribute->stock ?? $sub_element_distribute['stock'])) {
                                    $max_product = $sub_element_distribute->stock ?? $sub_element_distribute['stock'];
                                    $arr_id_rele[$product->id]['distribute_name'] =   $product->inventory['distributes'][0]->name;
                                    $arr_id_rele[$product->id]['element_distribute_name'] = $element_distribute->stock ?? $element_distribute['name'];
                                    $arr_id_rele[$product->id]['element_distribute_id'] = $element_distribute->id ??  $element_distribute['id'];
                                    $arr_id_rele[$product->id]['sub_element_distribute_id'] = $sub_element_distribute->id ??  $sub_element_distribute['id'];
                                    $arr_id_rele[$product->id]['sub_element_distribute_name'] = $sub_element_distribute->name ?? $sub_element_distribute['name'];
                                }

                                if ($max_product > $quantity) {
                                    continue;
                                }
                            }
                        }
                    }

                    if ($max_product == null || $max_product < $quantity) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::ERROR[0],
                            'msg' => "Sản phẩm '" . ($product->name) . "' trong combo đã hết hàng",
                        ], 400);
                    }
                } else {
                    $data_choose = ProductUtils::auto_choose_distribute($product, $arr_id_rele);
                    $arr_id_rele[$product->id] = $data_choose;
                }
            } else {
                $data_choose = ProductUtils::auto_choose_distribute($product, $arr_id_rele);
                $arr_id_rele[$product->id] = $data_choose;
            }
        }


        $list_cart_id = $oneCart == null ? null : $oneCart->id;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;



        foreach ($checkComboExists->products_combo as $product_combo) {
            $product = $product_combo->product;
            $quantity = $product_combo->quantity;


            $itemExists = null;
            //$items = CustomerCartController::all_items_cart($request, $oneCart->id);
            //Auto remove duplicate

            $product_id =   $product->id;
            $product_data_dis =  $arr_id_rele[$product->id] ?? null;
            $distribute_name =     $product_data_dis['distribute_name'] ?? null;
            $element_distribute_name =     $product_data_dis['element_distribute_name'] ?? null;
            $sub_element_distribute_name =    $product_data_dis['sub_element_distribute_name'] ?? null;
            $element_distribute_id =     $product_data_dis['element_distribute_id'] ?? null;
            $sub_element_distribute_id =    $product_data_dis['sub_element_distribute_id'] ?? null;

            $itemExists_arr = CcartItem::allItem($oneCart->id,  $request)
                ->where('is_bonus', false)
                ->where('product_id',  $product_id)
                ->where('element_distribute_id', $element_distribute_id)
                ->where('sub_element_distribute_id', $sub_element_distribute_id)
                ->get();


            if (count($itemExists_arr) > 0) {
                $itemExists = $itemExists_arr[0];

                //xoa duplicate
                $index_item = 0;
                foreach ($itemExists_arr as $item) {
                    if ($index_item != 0) {
                        $item->delete();
                    }
                    $index_item++;
                }
            }

            ////    ////  //// ////  //// ////  //// ////
            $next_quantity = $quantity;
            if (empty($itemExists)) {
                $next_quantity = $quantity;
            } else {
                $next_quantity = $itemExists->quantity + $quantity;
            }


            $distributes_add = $this->get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name);



            if (empty($itemExists)) {

                $lineItem = CcartItem::create(
                    [
                        'store_id' => $request->store->id,
                        'customer_id' =>  $customer_id,
                        'product_id' => $product_id,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id,
                        'quantity' =>  $next_quantity == 0 ? 1 :  $next_quantity,
                        'distributes' => json_encode($distributes_add),
                        'user_id' => $user_id,
                        'staff_id' => $staff_id,
                        'list_cart_id' => $list_cart_id,
                        'allows_choose_distribute' => true
                    ]
                );
            } else {

                $itemExists->update(
                    [
                        'product_id' => $product_id,
                        'element_distribute_id' => $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id,
                        'customer_id' =>  $customer_id,
                        'user_id' => $user_id,
                        'staff_id' => $staff_id,

                        'quantity' => $next_quantity ?? 1,
                        'distributes' =>  $distributes_add == null ? $itemExists->distributes :  json_encode($distributes_add),
                        'list_cart_id' => $list_cart_id,
                        'allows_choose_distribute' => true
                    ]
                );
            }
        }

        return $this->getOneCartV1($request);
    }



    /**
     * Chỉnh sửa giá tùy thích item price
     * 
     * @urlParam  store_code required Store code
     * @urlParam  cart_item_id required cart_item_id id
     * @bodyParam has_edit_item_price boolean có sửa giá hay không
     * @bodyParam item_price double giá mới
     * 
     */
    public function updatePriceCartItem(Request $request, $id)
    {
        $cart_item_id = $request->route()->parameter('cart_item_id');
        $cart_id = $request->route()->parameter('cart_id');

        $cCartItem = CcartItem::where('id', $cart_item_id)->where('list_cart_id', $cart_id)
            ->where('store_id', $request->store->id)
            ->first();

        if ($cCartItem  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                'msg' => MsgCode::NO_LINE_ITEMS[1],
            ], 400);
        }

        if ($request->item_price < 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PRICE[0],
                'msg' => MsgCode::INVALID_PRICE[1],
            ], 400);
        }

        $has_edit_item_price = filter_var($request->has_edit_item_price, FILTER_VALIDATE_BOOLEAN);

        $cCartItem->update([
            "has_edit_item_price" =>  $has_edit_item_price,
            "item_price" => $request->item_price
        ]);

        return $this->getOneCart($request);
    }

    /**
     * Chỉnh sửa giá tùy thích item price
     * 
     * @urlParam  store_code required Store code
     * @urlParam  cart_item_id required cart_item_id id
     * @bodyParam has_edit_item_price boolean có sửa giá hay không
     * @bodyParam item_price double giá mới
     * 
     */
    public function updateNoteCartItem(Request $request, $id)
    {
        $cart_item_id = $request->route()->parameter('cart_item_id');
        $cart_id = $request->route()->parameter('cart_id');

        $cCartItem = CcartItem::where('id', $cart_item_id)->where('list_cart_id', $cart_id)
            ->where('store_id', $request->store->id)
            ->first();

        if ($cCartItem  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                'msg' => MsgCode::NO_LINE_ITEMS[1],
            ], 400);
        }

        $cCartItem->update([
            "note" => $request->note
        ]);

        return $this->getOneCart($request);
    }

    /**
     * Cập nhật sản phẩm trong giỏ
     * 
     * @urlParam  store_code required Store code
     * @urlParam  branch_id required Branch id
     * @bodyParam product_id int required Product id
     * @bodyParam distribute_name string Tên kiểu phân loại
     * @bodyParam element_distribute_name string  Kiểu phân loại
     * @bodyParam sub_element_distribute_name string  Phân loại con
     * @bodyParam quantity int so luong  Phân loại con
     * @bodyParam line_item_id int line_item_id
     * 
     */
    public function updateLineItem(Request $request, $id)
    {
        $cart_id = $request->route()->parameter('cart_id');
        $line_item_id = $request->line_item_id;

        $cartItem  = CcartItem::where('id', $line_item_id)->where('store_id', $request->store->id)->first();


        $distribute_name = $request->distribute_name;
        $element_distribute_name = $request->element_distribute_name;
        $sub_element_distribute_name = $request->sub_element_distribute_name;

        $HAS_SUB = "HAS_SUB";
        $HAS_ELE = "HAS_ELE";
        $NO_ELE_SUB = "NO_ELE_SUB";

        $type_product =    $NO_ELE_SUB;
        $product_id = $request->product_id;
        $store_id = $request->store->id;

        $element_distribute_id = null;
        $sub_element_distribute_id = null;

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


        if ($productExists->distributes != null && count($productExists->distributes) > 0) {

            if (isset($productExists->distributes[0]->element_distributes) && count($productExists->distributes[0]->element_distributes) > 0) {

                if (count($productExists->distributes[0]->element_distributes[0]->sub_element_distributes) > 0) {
                    $type_product =    $HAS_SUB;
                } else {
                    $type_product =    $HAS_ELE;
                }
            }
        } else {
            $type_product =    $NO_ELE_SUB;
        }

        if ($type_product == $HAS_SUB) {

            $error_sub = false;
            if (empty($distribute_name) && empty($element_distribute_name) && empty($sub_element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];
                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name) || empty($sub_element_distribute_name)) {
                $error_sub = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_sub = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_sub = true;
                    } else {
                        $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                            ->where('distribute_id', $distribute->id)
                            ->where('element_distribute_id', $ele_distribute->id)
                            ->where('name', $sub_element_distribute_name)->where('store_id', $store_id)->first();

                        if ($sub_ele_distribute  == null) {
                            $error_sub = true;
                        } else {
                            $element_distribute_id = $ele_distribute->id;
                            $sub_element_distribute_id =  $sub_ele_distribute->id;
                        }
                    }
                }
            }

            if ($error_sub  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product == $HAS_ELE) {

            $distribute_name = $request->distribute_name;
            $element_distribute_name = $request->element_distribute_name;
            $sub_element_distribute_name = null;

            $error_ele = false;
            if (empty($distribute_name) && empty($element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];
                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name)) {
                $error_ele = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_ele = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_ele = true;
                    } else {
                        $element_distribute_id = $ele_distribute->id;
                    }
                }
            }

            if ($error_ele  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product == $NO_ELE_SUB) {
            $distribute_name = null;
            $element_distribute_name = null;
            $sub_element_distribute_name = null;
        }
        ////            ////        ////       ////        ////        ////
        if ($cartItem  == null) {  //Kiểm  tra line item
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                'msg' => MsgCode::NO_LINE_ITEMS[1],
            ], 404);


            if ($cartItem != null &&   $cartItem->is_bonus == true && $request->quantity != $cartItem->quantity) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng không thể thay đổi số lượng",
                ], 400);
            }

            if ($cartItem != null &&   $cartItem->is_bonus == true &&   $cartItem->allows_choose_distribute == false && ($element_distribute_id != $cartItem->element_distribute_id ||
                $sub_element_distribute_id != $cartItem->sub_element_distribute_id
            )) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng này không thể thay đổi phân loại",
                ], 400);
            }
        }

        $oneCart = $this->get_one_cart_default($request);

        if ($request->quantity == 0) {
            $cartItem->delete();
            $this->auto_update_code_voucher($oneCart, $request);
            return $this->getOneCart($request);
        }

        //-----------------------


        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
            $store_id,
            $request->branch->id,
            $product_id,

            $element_distribute_id,
            $sub_element_distribute_id
        );


        ///Xử lý số lượng tồn kho
        $max_quantity = $data_stock['stock'];



        $itemExists = null;
        $items = CustomerCartController::all_items_cart($request, $oneCart->id);

        foreach ($items as $item) {
            if (
                $request->product_id == $item->product_id &&
                $element_distribute_id  == $item->element_distribute_id &&
                $sub_element_distribute_id == $item->sub_element_distribute_id &&
                $sub_element_distribute_id == $item->sub_element_distribute_id &&
                $cartItem->id !=  $item->id
            ) {
                $itemExists = $item;
                break;
            };
        }



        ////

        $next_quantity = $request->quantity;

        $next_quantity = abs($request->quantity);

        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];


        if ($allow_semi_negative == false && $productExists->check_inventory == true && $next_quantity  > $max_quantity) {

            if ($max_quantity > 0) {
                $cartItem->update(
                    [
                        'quantity' => $max_quantity,
                    ]
                );
            }


            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[0],
                'msg' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[1],
            ], 400);
        }


        $distributes_add = $this->get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name);


        $cartItem->update(
            [
                'quantity' => $next_quantity,
                'product_id' => $product_id,
                'element_distribute_id' => $element_distribute_id,
                'sub_element_distribute_id' => $sub_element_distribute_id,
                'distributes' =>  $distributes_add == null ? $cartItem->distributes :  json_encode($distributes_add),
            ]
        );


        if ($itemExists != null) {
            $itemExists->delete();
        }

        


        $this->auto_update_code_voucher($oneCart, $request);

        return $this->getOneCart($request);
    }

    public function updateLineItemV1(Request $request, $id)
    {
        $cart_id = $request->route()->parameter('cart_id');
        $line_item_id = $request->line_item_id;

        $cartItem  = CcartItem::where('id', $line_item_id)->where('store_id', $request->store->id)->first();


        $distribute_name = $request->distribute_name;
        $element_distribute_name = $request->element_distribute_name;
        $sub_element_distribute_name = $request->sub_element_distribute_name;

        $HAS_SUB = "HAS_SUB";
        $HAS_ELE = "HAS_ELE";
        $NO_ELE_SUB = "NO_ELE_SUB";

        $type_product =    $NO_ELE_SUB;
        $product_id = $request->product_id;
        $store_id = $request->store->id;

        $element_distribute_id = null;
        $sub_element_distribute_id = null;

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


        if ($productExists->distributes != null && count($productExists->distributes) > 0) {

            if (isset($productExists->distributes[0]->element_distributes) && count($productExists->distributes[0]->element_distributes) > 0) {

                if (count($productExists->distributes[0]->element_distributes[0]->sub_element_distributes) > 0) {
                    $type_product =    $HAS_SUB;
                } else {
                    $type_product =    $HAS_ELE;
                }
            }
        } else {
            $type_product =    $NO_ELE_SUB;
        }

        if ($type_product == $HAS_SUB) {

            $error_sub = false;
            if (empty($distribute_name) && empty($element_distribute_name) && empty($sub_element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];
                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name) || empty($sub_element_distribute_name)) {
                $error_sub = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_sub = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_sub = true;
                    } else {
                        $sub_ele_distribute =    SubElementDistribute::where('product_id', $product_id)
                            ->where('distribute_id', $distribute->id)
                            ->where('element_distribute_id', $ele_distribute->id)
                            ->where('name', $sub_element_distribute_name)->where('store_id', $store_id)->first();

                        if ($sub_ele_distribute  == null) {
                            $error_sub = true;
                        } else {
                            $element_distribute_id = $ele_distribute->id;
                            $sub_element_distribute_id =  $sub_ele_distribute->id;
                        }
                    }
                }
            }

            if ($error_sub  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_SUB_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product == $HAS_ELE) {

            $distribute_name = $request->distribute_name;
            $element_distribute_name = $request->element_distribute_name;
            $sub_element_distribute_name = null;

            $error_ele = false;
            if (empty($distribute_name) && empty($element_distribute_name)) {
                $data_find = $this->find_distribute_auto($request, $productExists, 1);
                if ($data_find['success'] == true) {

                    $element_distribute_id = $data_find['element_distribute_id'];
                    $sub_element_distribute_id = $data_find['sub_element_distribute_id'];
                    $distribute_name = $data_find['distribute_name'];
                    $element_distribute_name = $data_find['element_distribute_name'];
                    $sub_element_distribute_name = $data_find['sub_element_distribute_name'];
                }
            } else if (empty($distribute_name) || empty($element_distribute_name)) {
                $error_ele = true;
            } else {
                $distribute =    Distribute::where('product_id', $product_id)
                    ->where('name', $distribute_name)->where('store_id', $store_id)->first();

                if ($distribute == null) {
                    $error_ele = true;
                } else {
                    $ele_distribute =    ElementDistribute::where('product_id', $product_id)
                        ->where('distribute_id', $distribute->id)
                        ->where('name', $element_distribute_name)->where('store_id', $store_id)->first();

                    if ($ele_distribute == null) {
                        $error_ele = true;
                    } else {
                        $element_distribute_id = $ele_distribute->id;
                    }
                }
            }

            if ($error_ele  == true) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[0],
                    'msg' => MsgCode::NO_ELEMENT_DISTRIBUTE_EXISTS[1],
                ], 404);
            }
        }

        if ($type_product == $NO_ELE_SUB) {
            $distribute_name = null;
            $element_distribute_name = null;
            $sub_element_distribute_name = null;
        }
        ////            ////        ////       ////        ////        ////
        if ($cartItem  == null) {  //Kiểm  tra line item
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                'msg' => MsgCode::NO_LINE_ITEMS[1],
            ], 404);


            if ($cartItem != null &&   $cartItem->is_bonus == true && $request->quantity != $cartItem->quantity) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng không thể thay đổi số lượng",
                ], 400);
            }

            if ($cartItem != null &&   $cartItem->is_bonus == true &&   $cartItem->allows_choose_distribute == false && ($element_distribute_id != $cartItem->element_distribute_id ||
                $sub_element_distribute_id != $cartItem->sub_element_distribute_id
            )) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Sản phẩm thưởng này không thể thay đổi phân loại",
                ], 400);
            }
        }

        $oneCart = $this->get_one_cart_default($request);

        if ($request->quantity == 0) {
            $cartItem->delete();
            $this->auto_update_code_voucher($oneCart, $request);
            return $this->getOneCartV1($request);
        }

        //-----------------------


        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
            $store_id,
            $request->branch->id,
            $product_id,

            $element_distribute_id,
            $sub_element_distribute_id
        );


        ///Xử lý số lượng tồn kho
        $max_quantity = $data_stock['stock'];



        $itemExists = null;
        $items = CustomerCartController::all_items_cart($request, $oneCart->id);

        foreach ($items as $item) {
            if (
                $request->product_id == $item->product_id &&
                $element_distribute_id  == $item->element_distribute_id &&
                $sub_element_distribute_id == $item->sub_element_distribute_id &&
                $sub_element_distribute_id == $item->sub_element_distribute_id &&
                $cartItem->id !=  $item->id
            ) {
                $itemExists = $item;
                break;
            };
        }



        ////

        $next_quantity = $request->quantity;

        $next_quantity = abs($request->quantity);

        $config = GeneralSettingController::defaultOfStore($request);
        $allow_semi_negative = $config['allow_semi_negative'];


        if ($allow_semi_negative == false && $productExists->check_inventory == true && $next_quantity  > $max_quantity) {

            if ($max_quantity > 0) {
                $cartItem->update(
                    [
                        'quantity' => $max_quantity,
                    ]
                );
            }


            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[0],
                'msg' => MsgCode::EXCEED_THE_QUANTITY_IN_STOCK[1],
            ], 400);
        }


        $distributes_add = $this->get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name);


        $cartItem->update(
            [
                'quantity' => $next_quantity,
                'product_id' => $product_id,
                'element_distribute_id' => $element_distribute_id,
                'sub_element_distribute_id' => $sub_element_distribute_id,
                'distributes' =>  $distributes_add == null ? $cartItem->distributes :  json_encode($distributes_add),
            ]
        );


        if ($itemExists != null) {
            $itemExists->delete();
        }

        


        $this->auto_update_code_voucher($oneCart, $request);

        return $this->getOneCartV1($request);
    }

    function get_distribute_array($distribute_name,  $element_distribute_name,    $sub_element_distribute_name)
    {

        $distributes_add = [];

        if (!empty($distribute_name)) {
            array_push($distributes_add, [
                'name' =>   $distribute_name,
                'value' => $element_distribute_name,
                'sub_element_distributes' =>  $sub_element_distribute_name,
            ]);
        }

        return  $distributes_add;
    }
}
