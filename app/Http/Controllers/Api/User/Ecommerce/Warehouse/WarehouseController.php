<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Warehouse;

use App\Helper\Ecommerce\EcommerceUtils;
use App\Helper\Ecommerce\LazadaUtils;
use App\Helper\Ecommerce\ShopeeUtils;
use App\Helper\Ecommerce\TikiUtils;
use App\Helper\Helper;
use App\Http\Controllers\Api\User\Ecommerce\Connect\ShopeeController;
use App\Http\Controllers\Controller;
use App\Models\EcommercePlatform;
use App\Models\EcommerceWarehouses;
use App\Models\MsgCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  User/Kết nối sàn kho hàng
 */
class WarehouseController extends Controller
{

    /**
     * Danh sách kho hàng
     * 
     * 
     * @queryParam shop_id shop id
     * 
     */
    public function getAll(Request $request)
    {
        $shop_id = request("shop_id");
        $now = Helper::getTimeNowDateTime();
        $list_in_ecommerce = array();


        $ecommercePlatform = EcommercePlatform::where('store_id',  $request->store->id)
            ->where('shop_id',   $shop_id)
            ->first();

        if ($ecommercePlatform  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[0],
                'msg' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[1],
            ], 400);
        }

        if ($ecommercePlatform != null &&  $ecommercePlatform->platform == "TIKI") {
            $dataRes =   TikiUtils::getAllInventory($ecommercePlatform->token);
            if (isset($dataRes->data)) {
                foreach ($dataRes->data as $item) {
                    array_push($list_in_ecommerce, [
                        "name" =>  $item->name,
                        "code" =>  $item->id,
                        "address" =>  $item->street,
                    ]);
                }
            }
        }

        if ($ecommercePlatform != null &&  $ecommercePlatform->platform == "LAZADA") {
            $dataRes =   LazadaUtils::getAllInventory($ecommercePlatform->token);
            if (isset($dataRes->result) && $dataRes->result->success == true) {
                foreach ($dataRes->result->module as $item) {
                    array_push($list_in_ecommerce, [
                        "name" =>  $item->detailAddress,
                        "code" =>  $item->code,
                        "address" =>  $item->detailAddress,
                    ]);
                }
            }
        }

        // if ($ecommercePlatform != null &&  $ecommercePlatform->platform == "SHOPEE") {
        //     if ($ecommercePlatform->expiry_token < $now) {
        //         ShopeeController::refresh_token($ecommercePlatform);
        //     }
        //     $dataRes =   ShopeeUtils::getAllInventory($ecommercePlatform);
        //     if (isset($dataRes->data)) {
        //         foreach ($dataRes->data as $item) {
        //             array_push($list_in_ecommerce, [
        //                 "name" =>  $item->name,
        //                 "code" =>  $item->id,
        //                 "address" =>  $item->street,
        //             ]);
        //         }
        //     }
        // }

        EcommerceWarehouses::whereNotIn('code', array_column($list_in_ecommerce, "code"))->where('store_id',  $request->store->id)
            ->where('shop_id',   $shop_id)->delete();

        foreach ($list_in_ecommerce as $item_in_ecom) {
            $itemExists =  EcommerceWarehouses::where('code', $item_in_ecom['code'])->where('store_id',  $request->store->id)
                ->where('shop_id',   $shop_id)->first();

            $itemSave = [
                'store_id' => $request->store->id,
                'shop_id' =>  $shop_id,
                "name" =>  $item_in_ecom['name'],
                "code" =>  $item_in_ecom['code'],
                "address" =>   $item_in_ecom['address'],
            ];

            if ($itemExists  == null) {
                EcommerceWarehouses::create($itemSave);
            } else {
                $itemExists->update($itemSave);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => EcommerceWarehouses::where('store_id',  $request->store->id)
                ->where('shop_id',  $shop_id)->get()
        ], 200);
    }

    /**
     * Cập nhật thông tin kho
     * 
     * 
     * @urlParam  warehouse_id kho id
     * @bodyParam allow_sync có đồng bộ qua kho này không
     * 
     */
    public function updateOne(Request $request)
    {
        $warehouse_id = $request->warehouse_id;

        $warehouseExists = EcommerceWarehouses::where('store_id',  $request->store->id)
            ->where('id',   $warehouse_id)
            ->first();

        if ($warehouseExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_WAREHOUSE_EXISTS[0],
                'msg' => MsgCode::NO_WAREHOUSE_EXISTS[1],
            ], 400);
        }

        $warehouseExists->update([
            'allow_sync' => $request->allow_sync
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $warehouseExists
        ], 200);
    }
}
