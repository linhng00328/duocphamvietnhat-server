<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\OrderShiperCode;
use App\Models\ProductReviews;
use App\Models\Shipment;
use App\Models\StoreAddress;
use App\Services\Shipper\GetHistoryStatusDelivery;
use App\Services\Shipper\SendOrderService;
use Illuminate\Http\Request;
use Exception;

/**
 * @group  Giao hàng/Đơn hàng cho nhà vận chuyển
 */

class SendOrderShipperController extends Controller
{
    /**
     * 
     * Gửi đơn hàng cho nhà vận chuyển đăng đơn hàng
     * 
     * @bodyParam order_code string code đơn hàng
     * @bodyParam branch_id int Id chi nhánh
     * 
     */

    public function sendOrderToShipper(Request $request, $id)
    {
        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->first();

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        if ($orderExists->partner_shipper_id === null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::ORDER_HAS_NOT_SELECTED_PARTNER[0],
                'msg' => MsgCode::ORDER_HAS_NOT_SELECTED_PARTNER[1],
            ], 404);
        }

        $partnerExists = Shipment::where('store_id', $request->store->id)
            ->where('partner_id', $orderExists->partner_shipper_id)
            ->where('use', true)
            ->whereNotNull('token')
            ->first();

        /////
        $error_msg = null;
        $data = null;
        /////

        $addressPickupExists = StoreAddress::where(
            'store_id',
            $request->store->id
        )
            ->when($orderExists->branch_id != null, function ($query)  use ($orderExists) {
                $query->where('branch_id', $orderExists->branch_id);
            })
            ->where('is_default_pickup', true)->first();

        if ($addressPickupExists  == null) {

            $addressPickupExists = StoreAddress::where(
                'store_id',
                $request->store->id
            )
                ->where('is_default_pickup', true)->first();

            if ($addressPickupExists != null) {
                $addressPickupExists->update([
                    'branch_id' => $request->branch_id
                ]);
            }
        }


        if (empty($addressPickupExists)) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => [],
                'msg_code' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[0],
                'msg' => MsgCode::STORE_HAS_NOT_SET_PICKUP_ADDRESS[1],
            ], 200);
        }

        if (!isset($partnerExists->token)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Chưa cài đặt token nhà vận chuyển",
            ], 400);
        }



        if ($orderExists->partner_shipper_id  === 0) {
            $res_send_data = SendOrderService::send_order_ghtk($orderExists,    $addressPickupExists,  $partnerExists->token);

            // [
            //     'data'
            //     'fee'
            // ]

            if ($res_send_data instanceof Exception) {
                $error_msg = $res_send_data->getMessage();
            } else {
                $data = $res_send_data;
            }
        } else if ($orderExists->partner_shipper_id  === 1) {
            $res_send_data = SendOrderService::send_order_ghn($orderExists,    $addressPickupExists,  $partnerExists->token);

            if ($res_send_data instanceof Exception) {
                $error_msg = $res_send_data->getMessage();
            } else {
                $data = $res_send_data;
            }
        } else if ($orderExists->partner_shipper_id  === 2) {
            $res_send_data = SendOrderService::send_order_vtp($orderExists, $addressPickupExists, $partnerExists->token);

            if ($res_send_data instanceof Exception) {
                $error_msg = $res_send_data->getMessage();
            } else {
                $data = $res_send_data;
            }
        } else if ($orderExists->partner_shipper_id  === 3) {
            $res_send_data = SendOrderService::send_order_vietnam_post($orderExists, $addressPickupExists, $partnerExists->token);
            if ($res_send_data instanceof Exception) {
                $error_msg = $res_send_data->getMessage();
            } else {
                $data = $res_send_data;
            }
        } else if ($orderExists->partner_shipper_id  === 4) {
            $res_send_data = SendOrderService::send_order_nhattin($orderExists, $addressPickupExists, $partnerExists->token);

            if ($res_send_data instanceof Exception) {
                $error_msg = $res_send_data->getMessage();
            } else {
                $data = $res_send_data;
            }
        } else {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_CONNECT_THE_CARRIER[0],
                'msg' => MsgCode::UNABLE_TO_CONNECT_THE_CARRIER[1],
            ], 404);
        }



        if ($error_msg != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => $error_msg,
            ], 400);
        }

        if ($data != null) {
            OrderShiperCode::create([
                'order_id' => $orderExists->id,
                "from_shipper_code" => $data['code'],
                "partner_id" => $orderExists->partner_shipper_id
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
