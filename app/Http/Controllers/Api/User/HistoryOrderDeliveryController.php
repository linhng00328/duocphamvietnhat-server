<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CollaboratorUtils;
use App\Helper\Helper;
use App\Helper\RefundUtitls;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\OrderShiperCode;
use App\Models\ProductReviews;
use App\Models\Shipment;
use App\Models\StoreAddress;
use App\Services\Shipper\GetHistoryStatusDelivery;
use App\Services\Shipper\SendOrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

/**
 * @group  Giao hàng/Đơn hàng cho nhà vận chuyển
 */

class HistoryOrderDeliveryController extends Controller
{

    /**
     * Lịch sử trạng thái đơn hàng từ nhà vận chuyển
     * 
     * @bodyParam order_code string code đơn hàng
     * 
     */
    public function getHistoryStatus(Request $request)
    {

        $array_status = array();

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

        $partnerExists = Shipment::where('store_id', $request->store->id)
            ->where('partner_id', $orderExists->partner_shipper_id)
            ->where('use', true)
            ->whereNotNull('token')
            ->first();


        $orderShipCode =  OrderShiperCode::where('order_id', $orderExists->id)
            ->where('partner_id',  $orderExists->partner_shipper_id)->first();

        if ($orderExists  != null && $orderShipCode != null && $partnerExists  != null) {

            array_push($array_status, [
                'time' => $orderShipCode->created_at->format('Y-m-d H:i:s'),
                'status_text' => "Đã gửi đơn cho giao vận",

            ]);

            if ($orderExists->partner_shipper_id  === 0) {
                $res = GetHistoryStatusDelivery::get_history_ghtk($orderExists, $partnerExists->token,  $orderShipCode->from_shipper_code);

                // 'time' => $jsonResponse->order->modified,
                // 'status_text' => $jsonResponse->order->status_text,
                // 'ship_money' =>  $jsonResponse->order->ship_money,
                if (!($res instanceof Exception)) {
                    array_push($array_status, $res);
                }
            }

            if ($orderExists->partner_shipper_id  === 1) {

                $res = GetHistoryStatusDelivery::get_history_ghn($orderExists, $partnerExists->token,  $orderShipCode->from_shipper_code);

                // 'time' => $jsonResponse->order->modified,
                // 'status_text' => $jsonResponse->order->status_text,
                // 'ship_money' =>  $jsonResponse->order->ship_money,
                if (!($res instanceof Exception)) {
                    array_push($array_status, $res);
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $array_status
        ], 200);
    }


    /**
     * Hủy liên kết vận chuyển
     * 
     * @bodyParam order_code string code đơn hàng
     * 
     */
    public function cancelOrderShipCode(Request $request)
    {
        $error_msg = null;
        $now = Carbon::now();

        $orderExists = Order::with('order_shipper_code')
            ->where('store_id', $request->store->id)
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

        $orderExists->update([
            'order_status' => StatusDefineCode::WAITING_FOR_PROGRESSING
        ]);
        $orderShipCode =  OrderShiperCode::where('order_id', $orderExists->id)
            ->where('partner_id',  $orderExists->partner_shipper_id)->first();

        try {
            $partnerExists = Shipment::where('store_id', $request->store->id)
                ->where('partner_id', $orderExists->partner_shipper_id)
                ->where('use', true)
                ->whereNotNull('token')
                ->first();

            /////
            $data = null;
            /////


            if (!isset($partnerExists->token)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Chưa cài đặt token nhà vận chuyển",
                ], 400);
            }


            if ($orderExists->partner_shipper_id  === 0) {
                //     $res_send_data = SendOrderService::send_order_ghtk($orderExists,    $addressPickupExists,  $partnerExists->token);

                //     // [
                //     //     'data'
                //     //     'fee'
                //     // ]

                //     if ($res_send_data instanceof Exception) {
                //         $error_msg = $res_send_data->getMessage();
                //     } else {
                //         $data = $res_send_data;
                //     }
            } else if ($orderExists->partner_shipper_id  === 1) {
                //     $res_send_data = SendOrderService::send_order_ghn($orderExists,    $addressPickupExists,  $partnerExists->token);

                //     // [
                //     //     'data'
                //     //     'fee'
                //     // ]

                //     if ($res_send_data instanceof Exception) {
                //         $error_msg = $res_send_data->getMessage();
                //     } else {
                //         $data = $res_send_data;
                //     }
            } else if ($orderExists->partner_shipper_id  === 2) {

                $res_send_data = SendOrderService::cancel_order_viettel_post($orderExists, $partnerExists->token);

                if ($res_send_data instanceof Exception) {
                    $error_msg = $res_send_data->getMessage();
                } else {
                    $data = $res_send_data;
                }
            } else if ($orderExists->partner_shipper_id  === 3) {
                if (isset($orderExists->order_shipper_code) && $orderExists->order_shipper_code != null) {
                    $timeCreateOrder = Carbon::parse($orderExists->order_shipper_code->created_at);
                } else {
                    $timeCreateOrder = Carbon::parse($orderExists->created_at);
                }

                if ($timeCreateOrder->gt($now->subSeconds(30))) { // đơn hàng vn post cần 15->25s xử lý nên là log ra thông báo ng dùng chờ time này

                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::ERROR[0],
                        'msg' => "Xin vui lòng thử lại trong ít phút hệ thống vận chuyển đang xử lý!",
                    ], 400);
                }

                $res_send_data = SendOrderService::cancel_order_vietnam_post($orderExists, $partnerExists->token);

                if ($res_send_data instanceof Exception) {
                    $error_msg = $res_send_data->getMessage();
                } else {
                    $data = $res_send_data;
                }
            } else if ($orderExists->partner_shipper_id  === 4) {
                $timeCreateOrder = Carbon::parse($orderExists->created_at);
                if ($timeCreateOrder->gt($now->subSeconds(30))) { // đơn hàng cần 10->15s xử lý nên là log ra thông báo ng dùng chờ time này
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::ERROR[0],
                        'msg' => "Xin vui lòng thử lại trong ít phút hệ thống vận chuyển đang xử lý!",
                    ], 400);
                }


                $res_send_data = SendOrderService::cancel_order_nhattin($orderExists, $partnerExists->token);
                if ($res_send_data instanceof Exception) {
                    $error_msg = $res_send_data->getMessage();
                } else {
                    $data = $res_send_data;
                }
            }

            if ($error_msg != null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $error_msg,
                ], 400);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($orderExists  != null && $orderShipCode != null && $error_msg == null) {
            $orderShipCode->delete();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Lấy trạng thái đơn hàng và cập nhật
     * 
     * @urlParam order_code string code hàng
     * @bodyParam allow_update bool cho phép update trạng thái đơn hàng
     * 
     */
    public function orderAndPaymentStatus(Request $request)
    {
        $allow_update = filter_var($request->allow_update, FILTER_VALIDATE_BOOLEAN);

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

        $partnerExists = Shipment::where('store_id', $request->store->id)
            ->where('partner_id', $orderExists->partner_shipper_id)
            ->where('use', true)
            ->whereNotNull('token')
            ->first();


        $orderShipCode =  OrderShiperCode::where('order_id', $orderExists->id)
            ->where('partner_id',  $orderExists->partner_shipper_id)->first();

        if ($orderShipCode == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Chưa gửi đơn hàng cho bên vận chuyển",
            ], 400);
        }

        if ($partnerExists == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Chưa cài đặt token vận chuyển",
            ], 400);
        }

        $data = [
            'payment_status' =>  null,
            'payment_status_name' =>  null,
            'order_status' =>  null,
            'order_status_name' =>  null,
        ];

        if ($orderExists->partner_shipper_id  === 0) {
            $res = GetHistoryStatusDelivery::status_from_delivery_to_local_statusGHTK($orderExists, $partnerExists->token,  $orderShipCode->from_shipper_code);

            if (!($res instanceof Exception)) {
                $data = $res;
            }
        }

        if ($orderExists->partner_shipper_id  === 1) {

            $res = GetHistoryStatusDelivery::status_from_delivery_to_local_statusGHN($orderExists, $partnerExists->token,  $orderShipCode->from_shipper_code);

            if (!($res instanceof Exception)) {
                $data = $res;
            }
        }



        if ($allow_update  == true) {
            if (isset($data['order_status'])) {

                $data['order_status_name'] =   StatusDefineCode::getOrderStatusCode($data['order_status'], true);
                $orderExists->update(
                    [
                        "order_status" => $data['order_status'],
                    ]
                );
            }

            if (isset($data['payment_status'])) {
                $data['payment_status_name'] =   StatusDefineCode::getPaymentStatusCode($data['payment_status'], true);
                $orderExists->update(
                    [
                        "payment_status" => $data['payment_status']
                    ]
                );
            }


            RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
            RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
            RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
            RefundUtitls::auto_refund_point_for_customer($orderExists, $request);
            RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);
            CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);
            OrderController::sub_inventory($orderExists);
        }

        $data['order_code'] =   $orderExists->order_code;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 200);
    }
}
