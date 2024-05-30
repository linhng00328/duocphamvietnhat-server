<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\MsgCode;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @group  Customer/Yêu thích sản phẩm
 */

class CustomerFavoriteController extends Controller
{
    /**
     * Yêu thích sản phẩm
     * @urlParam product_id string required product_id
     * @bodyParam is_favorite yêu thích hay không
     */
    public function favorite(Request $request)
    {

        $product_id = $request->route()->parameter('product_id') ?? null;

        $productExists = Product::where(
            'id',
            $product_id
        )->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($productExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }


        $is_like =  filter_var($request->is_favorite, FILTER_VALIDATE_BOOLEAN);

        $favoriteExists = Favorite::where('store_id', $request->store->id)
            ->where('product_id', $product_id)
            ->where('customer_id', $request->customer->id)
            ->first();
        if ($is_like == true) {
            if ($favoriteExists == null) {
                Favorite::create(
                    [
                        "store_id" =>   $request->store->id,
                        "customer_id" =>   $request->customer->id,
                        "product_id" => $productExists->id,
                    ]
                );
            }
        } else {
            Favorite::where('store_id', $request->store->id)
                ->where('product_id', $product_id)
                ->where('customer_id', $request->customer->id)->delete();
        }

        $productExists->update([
            'likes' => Favorite::where('product_id',  $productExists->id)->get()->count()
        ]);

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 201);
    }
}
