<?php

namespace App\Http\Middleware;

use App\Models\LinkBackPay;
use App\Models\MsgCode;
use App\Models\Order;
use Closure;

class CheckOrderPaid
{
    public function handle($request, Closure $next)
    {
        $order_code = $request->route()->parameter('order_code') ?? null;
        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $order_code)
            ->first();


        $linkBackExists = LinkBackPay::where('order_id',  $orderExists->id)->first();
        $link_back = null;
        if (!empty($linkBackExists->link_back)) {
            $link_back  = $linkBackExists->link_back;
        }

        if ($orderExists->payment_status == 2) {
            return response()->view('paid', [
                'order_code' => $order_code,
                'link_back' => $link_back,
            ]);
        } else {

            $request->merge([
                'order' => $orderExists,
            ]);

            return $next($request);
        }
    }
}
