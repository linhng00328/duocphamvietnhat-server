<?php

namespace App\Http\Middleware;

use App\Models\MsgCode;
use App\Models\Order;
use Closure;

class HasOrder
{
    public function handle($request, Closure $next)
    {
        $order_code = $request->route()->parameter('order_code') ?? null;
        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $order_code)
            ->first();

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        } else {

            $request->merge([
                'order' => $orderExists,
            ]);

            return $next($request);
        }
    }
}
