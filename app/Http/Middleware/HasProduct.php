<?php

namespace App\Http\Middleware;

use App\Models\MsgCode;
use App\Models\Product;
use App\Models\SessionCustomer;
use App\Models\ViewerProduct;
use Closure;


class HasProduct
{
    public function handle($request, Closure $next)
    {
        $id = $request->route()->parameter('product_id');
        $checkProductExists = Product::where(
            'id',
            $id
        )->where(
            'store_id',
            $request->store->id
        )->with('categories','category_children')->first();

        if (empty($checkProductExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        } else {

            $request->merge([
                'product' => $checkProductExists,
            ]);

            return $next($request);
        }
    }
}
