<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\ElementDistribute;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\SubElementDistribute;
use Illuminate\Http\Request;

/**
 * @group  Customer/Scan Qr Barcode
 */
class CustomerScanController extends Controller
{
    /**
     * Tìm sản phẩm theo mã barcode
     * 
     * @bodyParam barcode code barcode
     * 
     */
    public function productByBarcode(Request $request)
    {

        $distributes = array();
        $distribute = null;
        $quantity_in_stock = -1;

        if ($request->barcode == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::BARCODE_IS_REQUIRED[0],
                'msg' => MsgCode::BARCODE_IS_REQUIRED[1],
            ], 400);
        }
        $product = Product::where('store_id', $request->store->id)->where('barcode', $request->barcode)->first();
        $quantity_in_stock = $product != null ? $product->quantity_in_stock :  $quantity_in_stock;
        if ($product == null) {
            $ele = ElementDistribute::where('store_id', $request->store->id)->where('barcode', $request->barcode)->first();
            if ($ele != null) {
                $product = Product::where('store_id', $request->store->id)->where('id',  $ele->product_id)->first();
                $distribute = [
                    "name" => $ele->distribute->name,
                    "value" => $ele->name,
                ];
                $quantity_in_stock = $ele->quantity_in_stock;
            } else {
                $sub = SubElementDistribute::where('store_id', $request->store->id)->where('barcode', $request->barcode)->first();
                if ($sub != null) {
                    $product = Product::where('store_id', $request->store->id)->where('id',   $sub->product_id)->first();

                    $distribute = [
                        "name" =>  $sub->distribute->name,
                        "value" => $sub->element_distribute->name,
                        "sub_element_distributes" =>  $sub->name
                    ];
                    $quantity_in_stock = $sub->quantity_in_stock;
                }
            }
        }

        if ($product == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_PRODUCT_EXISTS[0],
                'msg' => MsgCode::NO_PRODUCT_EXISTS[1],
            ], 404);
        }

        if ($distribute != null) {
            array_push($distributes, $distribute);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'distributes' => $distributes,
                'quantity_in_stock' =>    $quantity_in_stock,
                'product' =>  $product
            ]
        ], 200);
    }
}
