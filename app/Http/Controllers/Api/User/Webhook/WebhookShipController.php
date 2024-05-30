<?php

namespace App\Http\Controllers\Api\User\WebHook;

use App\Helper\CollaboratorUtils;
use App\Helper\PointCustomerUtils;
use App\Helper\RefundUtitls;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Api\User\OrderController;
use App\Http\Controllers\Controller;
use App\Models\AgencyBonusStep;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\WebhookHistory;
use Exception;
use Illuminate\Http\Request;


/**
 * @group User/Webhook ship
 * 
 * Webhook
 * 
 */
class WebhookShipController extends Controller
{

    /**
     * Nhận dữ liệu từ giao vận
     * @urlParam  store_code required Store code
     * @bodyParam limit double required Giới hạn được thưởng
     * @bodyParam bonus double required Số tiền thưởng
     */
    public function create(Request $request)
    {

        $json = null;
        $partner_shipper_id =  null;
        $order_status = null;
        $order_code = null;
        $has_updated = false;

        if ($request->DATA != null && ($request->DATA['ORDER_NUMBER'] ?? null) != null) {

            $ORDER_NUMBER = $request->DATA['ORDER_NUMBER'];
            $ORDER_STATUS = $request->DATA['ORDER_STATUS'];
            $ORDER_REFERENCE = $request->DATA['ORDER_REFERENCE'];


            $order_code =   $ORDER_REFERENCE;
            $orderExists = Order::where('order_code',   $order_code)->first();



            if ($orderExists != null) {
                if ($orderExists->partner_shipper_id == 2) {
                    $arr_data_define_status_order = [
                        -100   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đơn hàng mới tạo, chưa duyệt
                        -108   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đơn hàng gửi tại bưu cục
                        -109   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đơn hàng đã gửi tại điểm thu gom
                        -110   => StatusDefineCode::WAITING_FOR_PROGRESSING, // Đơn hàng đang bàn giao qua bưu cục
                        100    => StatusDefineCode::SHIPPING, //Tiếp nhận đơn hàng từ đối tác "Viettelpost xử lý đơn hàng"
                        101    => StatusDefineCode::SHIPPING, //ViettelPost yêu cầu hủy đơn hàng
                        102    => StatusDefineCode::SHIPPING, //Đơn hàng chờ xử lý
                        103    => StatusDefineCode::SHIPPING, //Giao cho bưu cục "Viettelpost xử lý đơn hàng"
                        104    => StatusDefineCode::SHIPPING, // Giao cho Bưu tá đi nhận
                        105    => StatusDefineCode::SHIPPING, //Buu Tá đã nhận hàng
                        106    => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đối tác yêu cầu lấy lại hàng
                        107    => StatusDefineCode::USER_CANCELLED, //Đối tác yêu cầu hủy qua API
                        200    => StatusDefineCode::SHIPPING, //Nhận từ bưu tá - Bưu cục gốc
                        201    => StatusDefineCode::USER_CANCELLED, //Hủy nhập phiếu gửi
                        202    => StatusDefineCode::SHIPPING, //Sửa phiếu gửi
                        300    => StatusDefineCode::SHIPPING, //Close delivery file
                        301    => StatusDefineCode::SHIPPING, //Ðóng túi gói "Vận chuyển đi từ"
                        302    => StatusDefineCode::SHIPPING, //Đóng chuyến thư "Vận chuyển đi từ"
                        303    => StatusDefineCode::SHIPPING, //Đóng tuyến xe "Vận chuyển đi từ"
                        400    => StatusDefineCode::SHIPPING, //Nhận bảng kê đến "Nhận tại"
                        401    => StatusDefineCode::SHIPPING, //Nhận Túi gói "Nhận tại"
                        402    => StatusDefineCode::SHIPPING, //Nhận chuyến thư "Nhận tại"
                        403    => StatusDefineCode::SHIPPING, //Nhận chuyến xe "Nhận tại"
                        500    => StatusDefineCode::SHIPPING, //Giao bưu tá đi phát
                        501    => StatusDefineCode::COMPLETED, //Thành công - Phát thành công
                        502    => StatusDefineCode::CUSTOMER_HAS_RETURNS, //Chuyển hoàn bưu cục gốc
                        503    => StatusDefineCode::CUSTOMER_CANCELLED, //Hủy - Theo yêu cầu khách hàng
                        504    => StatusDefineCode::CUSTOMER_HAS_RETURNS, //Thành công - Chuyển trả người gửi
                        505    => StatusDefineCode::CUSTOMER_RETURNING, //Tồn - Thông báo chuyển hoàn bưu cục gốc
                        506    => StatusDefineCode::SHIPPING, //Tồn - Khách hàng nghỉ, không có nhà
                        507    => StatusDefineCode::SHIPPING, //Tồn - Khách hàng đến bưu cục nhận
                        508    => StatusDefineCode::SHIPPING, //Phát tiếp
                        509    => StatusDefineCode::SHIPPING, //Chuyển tiếp bưu cục khác
                        510    => StatusDefineCode::SHIPPING, //Hủy phân công phát
                        515    => StatusDefineCode::SHIPPING, //Bưu cục phát duyệt hoàn
                        550    => StatusDefineCode::SHIPPING, //Đơn Vị Yêu Cầu Phát Tiếp
                    ];

                    $arr_data_define_status_payment = [
                        501 => StatusDefineCode::PAID,
                    ];

                    if ($arr_data_define_status_order[$ORDER_STATUS] != null) {

                        $order_status = $arr_data_define_status_order[$ORDER_STATUS];
                        $orderExists->update([
                            'order_status' => $order_status
                        ]);

                        $has_updated  = true;
                    }

                    if (isset($arr_data_define_status_payment[$ORDER_STATUS])) {
                        $payment_status = $arr_data_define_status_payment[$ORDER_STATUS];
                        $orderExists->update([
                            'payment_status' => $payment_status
                        ]);

                        $has_updated  = true;
                    }
                }
            }
        }

        $content = $request->getContent();

        WebhookHistory::create([
            'order_code' => $order_code,
            'json' =>  json_encode($content),
            'order_status' =>  $order_status,
            'has_updated' => $has_updated,
            'type' => WebhookHistory::TYPE_VIETTEL_POST
        ]);

        PointCustomerUtils::bonus_point_from_order($request, $orderExists);
        RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
        RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
        RefundUtitls::auto_refund_point_for_customer($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);

        OrderController::sub_inventory($orderExists);
        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);

        if ($order_status  ==  StatusDefineCode::COMPLETED) { // Nếu trạng thái chuyển sang đã hoàn thành và đã thanh toán
            PointCustomerUtils::bonus_point_for_agency_product_from_order($request, $orderExists);
        }
        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'detail' => [
                'order_status' =>  $order_status
            ]
        ], 200);
    }


    /**
     * Nhận dữ liệu từ giao vận
     * @urlParam  store_code required Store code
     * @bodyParam limit double required Giới hạn được thưởng
     * @bodyParam bonus double required Số tiền thưởng
     */
    public function orderVietnamPost(Request $request)
    {

        $json = null;
        $partner_shipper_id =  null;
        $order_status = null;
        $order_code = null;
        $has_updated = false;

        if ($request->data != null && is_array($request->data)) {
            foreach ($request->data as $order) {
                $ORDER_CODE = $order['saleOrderCode'];
                $ORDER_STATUS = $order['status'];

                $orderExists = Order::where('order_code', $ORDER_CODE)->first();


                if ($orderExists != null) {
                    if ($orderExists->partner_shipper_id == 3) { //check vietel
                        $arr_data_define_status_order = [
                            0   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Lưu nháp
                            1   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đã tạo
                            2   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Chuyển tin cho Bưu tá
                            3   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đang xử lý
                            4   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đang lấy hàng
                            5   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Lấy hàng không thành công
                            6   => StatusDefineCode::SHIPPING, //Lấy hàng thành công	
                            7   => StatusDefineCode::SHIPPING, //Bưu cục đã nhận hàng
                            8   => StatusDefineCode::USER_CANCELLED, //Lấy hàng thất bại
                            9   => StatusDefineCode::USER_CANCELLED, //Hủy thu gom
                            22   => StatusDefineCode::USER_CANCELLED, //Hủy giao hàng
                            10   => StatusDefineCode::SHIPPING, //Đang vận chuyển
                            11   => StatusDefineCode::SHIPPING, //Đã đến BC phát
                            12   => StatusDefineCode::SHIPPING, //Đang giao hàng 
                            13   => StatusDefineCode::SHIPPING, //Chuyển tiếp
                            14   => StatusDefineCode::COMPLETED, //Đã giao hàng
                            15   => StatusDefineCode::SHIPPING, //Giao hàng không thành công
                            16   => StatusDefineCode::SHIPPING, //Chờ để chuyển hoàn
                            17   => StatusDefineCode::SHIPPING, //Đã duyệt hoàn
                            18   => StatusDefineCode::SHIPPING, //Bắt đầu chuyển hoàn
                            19   => StatusDefineCode::CUSTOMER_HAS_RETURNS, //Hoàn thành công
                            20   => StatusDefineCode::SHIPPING, //Đã trả hàng 1 phần
                            21   => StatusDefineCode::SHIPPING, //Đã trả tiền COD
                        ];

                        $arr_data_define_status_payment = [
                            14 => StatusDefineCode::PAID,
                        ];

                        if ($arr_data_define_status_order[$ORDER_STATUS] != null) {

                            $order_status = $arr_data_define_status_order[$ORDER_STATUS];
                            $orderExists->update([
                                'order_status' => $order_status
                            ]);

                            $has_updated  = true;
                        }

                        if (isset($arr_data_define_status_payment[$ORDER_STATUS])) {
                            $payment_status = $arr_data_define_status_payment[$ORDER_STATUS];
                            $orderExists->update([
                                'payment_status' => $payment_status
                            ]);

                            $has_updated  = true;
                        }
                    }
                }
            }
        }

        $content = $request->getContent();

        WebhookHistory::create([
            'order_code' => $order_code,
            'json' =>  json_encode($content),
            'order_status' =>  $order_status,
            'has_updated' => $has_updated,
            'type' => WebhookHistory::TYPE_VIETNAM_POST
        ]);

        PointCustomerUtils::bonus_point_from_order($request, $orderExists);
        RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
        RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
        RefundUtitls::auto_refund_point_for_customer($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);
        OrderController::sub_inventory($orderExists);
        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);

        if ($order_status  ==  StatusDefineCode::COMPLETED) { // Nếu trạng thái chuyển sang đã hoàn thành và đã thanh toán
            PointCustomerUtils::bonus_point_for_agency_product_from_order($request, $orderExists);
        }
        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'detail' => [
                'order_status' =>  $order_status
            ]
        ], 200);
    }

    /**
     * Nhận dữ liệu từ giao vận
     * @urlParam  store_code required Store code
     * @bodyParam limit double required Giới hạn được thưởng
     * @bodyParam bonus double required Số tiền thưởng
     */
    public function orderNhattinPost(Request $request)
    {
        $json = null;
        $partner_shipper_id =  null;
        $order_status = null;
        $ORDER_CODE = null;
        $has_updated = false;
        $data = isset($request->data) ? $request->data : $request;
        $orderExists = null;

        try {
            if ($request->data != null && is_array($request->data)) {
                $ORDER_CODE = $data->ref_code;
                $ORDER_STATUS = $data->status_id;

                $orderExists = Order::where('order_code', $ORDER_CODE)->first();

                if ($orderExists != null) {
                    if ($orderExists->partner_shipper_id == 4) { //check nhattin post
                        $arr_data_define_status_order = [
                            1   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Đã tạo
                            2   => StatusDefineCode::WAITING_FOR_PROGRESSING, //Chuyển tin cho Bưu tá
                            3   => StatusDefineCode::SHIPPING, //Đang xử lý
                            4   => StatusDefineCode::COMPLETED, //Đang lấy hàng
                            6   => StatusDefineCode::USER_CANCELLED, //Lấy hàng thành công	
                            7   => StatusDefineCode::SHIPPING, //Bưu cục đã nhận hàng
                            9   => StatusDefineCode::USER_CANCELLED, //Hủy thu gom
                            10   => StatusDefineCode::CUSTOMER_HAS_RETURNS, //Đang vận chuyển
                            11   => StatusDefineCode::SHIPPING, //Đã đến BC phát
                            12   => StatusDefineCode::SHIPPING, //Đang giao hàng 
                            13   => StatusDefineCode::SHIPPING, //Chuyển tiếp
                            15   => StatusDefineCode::SHIPPING, //Giao hàng không thành công
                            16   => StatusDefineCode::SHIPPING, //Chờ để chuyển hoàn
                        ];

                        $arr_data_define_status_payment = [
                            4 => StatusDefineCode::PAID,
                        ];

                        if ($arr_data_define_status_order[$ORDER_STATUS] != null) {

                            $order_status = $arr_data_define_status_order[$ORDER_STATUS];
                            $orderExists->update([
                                'order_status' => $order_status
                            ]);

                            $has_updated  = true;
                        }

                        if (isset($arr_data_define_status_payment[$ORDER_STATUS])) {
                            $payment_status = $arr_data_define_status_payment[$ORDER_STATUS];
                            $orderExists->update([
                                'payment_status' => $payment_status
                            ]);

                            $has_updated  = true;
                        }
                    }
                }
            }
        } catch (Exception $e) {
        }

        WebhookHistory::create([
            'order_code' => $data->ref_code,
            'json' =>  json_encode($request->json()->all()),
            'order_status' =>  $order_status,
            'has_updated' => $has_updated,
            'type' => WebhookHistory::TYPE_NHATTIN_POST
        ]);

        if ($orderExists != null) {
            PointCustomerUtils::bonus_point_from_order($request, $orderExists);
            RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
            RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
            RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
            RefundUtitls::auto_refund_point_for_customer($orderExists, $request);
            RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);

            OrderController::sub_inventory($orderExists);
            CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);

            if ($order_status  ==  StatusDefineCode::COMPLETED) { // Nếu trạng thái chuyển sang đã hoàn thành và đã thanh toán
                PointCustomerUtils::bonus_point_for_agency_product_from_order($request, $orderExists);
            }
            SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'detail' => [
                'order_status' =>  $order_status
            ]
        ], 200);
    }
}
