<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\User\CartController;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\SessionCustomer;
use Closure;
use Illuminate\Http\Request;


class HandlePriceAgency
{

    //Xử lý chuyển thông tin customer ko bắt buộc log
    public function handle(Request $request, Closure $next)
    {
        $cus =  null;
        if (!empty($request->customer_id)) {
            $cus = Customer::where('id', $request->customer_id)
                ->where('store_id', $request->store->id)
                ->first();
        } else
        if (!empty($request->customer_phone)) {
            $cus = Customer::where('phone_number', $request->customer_phone)
                ->where('store_id', $request->store->id)
                ->first();
        } else {
            $oneCart = CartController::get_one_cart_default($request);

            if ($oneCart  != null) {

                if ($oneCart->customer_id != null) {
                    $cus = Customer::where('id', $oneCart == null ? null : $oneCart->customer_id)
                        ->where('store_id', $oneCart->store_id)
                        ->first();
                } else {
                    $cus = Customer::where('phone_number', $oneCart == null ? null : $oneCart->customer_phone)
                        ->where('store_id', $oneCart->store_id)
                        ->first();
                }
            }
        }

        $request->merge([
            'customer' => $cus == null ? null : $cus,
        ]);


        return $next($request);
    }
}
