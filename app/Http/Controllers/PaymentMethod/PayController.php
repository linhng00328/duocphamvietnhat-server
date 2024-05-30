<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\LinkBackPay;
use Illuminate\Http\Request;


/**
 * @group  Customer/thanh toán
 */
class PayController extends Controller
{

    /**
     * Danh sách bài viết
     * @urlParam  store_code required Store code cần lấy
     * @urlParam  order_code required Mã đơn hàng
     */
    public function pay(Request $request)
    {
        // 1 ngân hàng
        // 2 vppay
        $store_code = $request->store->store_code;
        $order_code = $request->order->order_code;

        $linkBackExists = LinkBackPay::where('order_id', $request->order->id)->first();
        if ($linkBackExists  == null) {
            LinkBackPay::create([
                "order_id" => $request->order->id,
                "store_id" => $request->store->id,
                "link_back" => $request->link_back,
            ]);
        } else {
            $linkBackExists->update(
                [
                    "order_id" => $request->order->id,
                    "store_id" => $request->store->id,
                    "link_back" => $request->link_back,
                ]
            );
        }

        $payment_method_id = $request->order->payment_partner_id;

        if ($payment_method_id === null) {
            $payment_method_id = $request->order->payment_method_id;
        }

        if ($payment_method_id == 1) {

            return redirect("/api/customer/$store_code/purchase/pay/$order_code/bank");
        }
        if ($payment_method_id == 2) {
            return redirect("/api/customer/$store_code/purchase/pay/$order_code/vn_pay");
        }
        if ($payment_method_id == 3) {
            return redirect("/api/customer/$store_code/purchase/pay/$order_code/one_pay");
        }
        if ($payment_method_id == 4) {
            return redirect("/api/customer/$store_code/purchase/pay/$order_code/momo");
        }

    }
}
