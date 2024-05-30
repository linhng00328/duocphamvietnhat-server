<?php

namespace App\Http\Controllers\Api\User\Ecommerce\Connect;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\EcommercePlatform;
use App\Models\MsgCode;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

/**
 * @group  User/Kết nối sàn
 */
class ConnectController extends Controller
{


    /**
     * Danh sách đã kết nối
     * 
     * @queryParam 
     * 
     */
    public function connect_list(Request $request)
    {
        $listPlatformEcommerce = EcommercePlatform::where('store_id',  $request->store->id)
            ->when($request->platform_name != null, function ($query) use ($request) {
                $query->where('platform', $request->platform_name);
            })
            ->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $listPlatformEcommerce
        ], 200);
    }

    /**
     * Danh sách đã kết nối
     * 
     * @queryParam 
     * 
     */
    public function getOne(Request $request)
    {
        $platformEcommerceExists = EcommercePlatform::where('store_id',  $request->store->id)
            ->where('shop_id', $request->shop_id)
            ->first();

        if ($platformEcommerceExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[0],
                'msg' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $platformEcommerceExists
        ], 200);
    }



    /**
     * Cập nhật thông số kết nối
     * 
     * @queryParam 
     * 
     */
    public function updateOne(Request $request)
    {
        $platformEcommerceExists = EcommercePlatform::where('store_id',  $request->store->id)
            ->where('shop_id', $request->shop_id)
            ->first();

        if ($platformEcommerceExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[0],
                'msg' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $platformEcommerceExists->update([
            "shop_name" => $request->shop_name,
            "type_sync_products" => $request->type_sync_products,
            "type_sync_inventory" => $request->type_sync_inventory,
            "type_sync_orders" => $request->type_sync_orders,
            "customer_name" => $request->customer_name,
            "customer_phone" => $request->customer_phone,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $platformEcommerceExists
        ], 200);
    }


    /**
     * Gỡ bỏ kết nối 1 sàn
     * 
     * @queryParam 
     * 
     */
    public function deleteOne(Request $request)
    {
        $platformEcommerceExists = EcommercePlatform::where('store_id',  $request->store->id)
            ->where('shop_id', $request->shop_id)
            ->first();

        if ($platformEcommerceExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[0],
                'msg' => MsgCode::NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $platformEcommerceExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
