<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\AgencyUtils;
use App\Helper\CollaboratorUtils;
use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\CollaboratorsConfig;
use App\Models\Customer;
use App\Models\HistoryPayOrder;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\OrderShiperCode;
use App\Models\PointSetting;
use App\Models\Product;
use App\Services\BalanceCustomerService;
use App\Helper\PointCustomerUtils;
use App\Helper\RefundUtitls;
use App\Helper\SaveOperationHistoryUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\TypeAction;
use App\Models\Branch;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @group  User/Đơn hàng
 *
 * APIs Đơn hàng
 */
class OrderController extends Controller
{
    /**
     * Danh sách Order
     * Trạng thái đơn hàng saha
     * - Chờ xử lý (WAITING_FOR_PROGRESSING)
     * - Đang chuẩn bị hàng (PACKING)
     * - Hết hàng (OUT_OF_STOCK)
     * - Shop huỷ (USER_CANCELLED)
     * - Khách đã hủy (CUSTOMER_CANCELLED)
     * - Đang giao hàng (SHIPPING)
     * - Lỗi giao hàng (DELIVERY_ERROR)
     * - Đã hoàn thành (COMPLETED)
     * - Chờ trả hàng (CUSTOMER_RETURNING)
     * - Đã trả hàng (CUSTOMER_HAS_RETURNS)
     * ############################################################################
     * Trạng thái thanh toán
     * - Chưa thanh toán (UNPAID)
     * - Chờ xử lý (WAITING_FOR_PROGRESSING)
     * - Đã thanh toán (PAID)
     * - Đã thanh toán một phần (PARTIALLY_PAID)
     * - Đã hủy (CANCELLED)
     * - Đã hoàn tiền (REFUNDS)
     * @urlParam  store_code required Store code. Example: kds
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  field_by Chọn trường nào để lấy
     * @queryParam  field_by_value Giá trị trường đó
     * @queryParam  time_from Từ thời gian nào
     * @queryParam  time_to Đến thời gian nào.
     * @queryParam  Kiểu gom thời gian type_query_time (created_at, last_time_change_order_status)
     * @queryParam  order_status_code trạng thái order
     * @queryParam  payment_status_code trạng thái thanh toán
     * @queryParam  collaborator_by_customer_id CTV theo customer id
     * @queryParam  agency_by_customer_id Dai ly theo customer id
     * @queryParam from_pos boolean From pos
     * @queryParam  phone_number sdt khach hang
     * @queryParam branch_id int Branch_id chi nhánh
     * @queryParam order_from_list List danh sách order từ nguồn nào VD: 2,3
     * @queryParam branch_id_list List danh sách chi nhánh VD: 1,2,3
     * @queryParam order_status_list List danh sách trạng thái đơn hàng: VD: 1,2
     * @queryParam payment_status_list List danh sách trạng thái thanh toán : VD: 2,3
     * @queryParam order_status_code_list List danh sách trạng thái đơn hàng: VD: 1,2
     * @queryParam payment_status_code_list List danh sách trạng thái thanh toán : VD: 2,3
     * @queryParam is_export có phải xuất exel không (true sẽ đầy đủ thông tin hơn)
     * 
     */
    public function getAll(Request $request, $id)
    {
        $order_from_list = request("order_from_list") == null ? [] : explode(',', str_replace(" ", "", request("order_from_list")));
        $branch_id_list = request("branch_id_list") == null ? [] : explode(',',  str_replace(" ", "", request("branch_id_list")));

        $order_status_list = request("order_status_list") == null ? [] : explode(',',  str_replace(" ", "", request("order_status_list")));
        $payment_status_list = request("payment_status_list") == null ? [] : explode(',',  str_replace(" ", "", request("payment_status_list")));

        $order_status_code_list = request("order_status_code_list") == null ? [] : explode(',',  str_replace(" ", "", request("order_status_code_list")));
        $payment_status_code_list = request("payment_status_code_list") == null ? [] : explode(',',  str_replace(" ", "", request("payment_status_code_list")));

        $order_status_code_list2 = [];
        $payment_status_code_list2 = [];

        foreach ($order_status_code_list as $item) {
            array_push($order_status_code_list2,  StatusDefineCode::getOrderStatusNum($item));
        }
        foreach ($payment_status_code_list as $item) {
            array_push($payment_status_code_list2,  StatusDefineCode::getPaymentStatusNum($item));
        }


        $type_query_time = request('type_query_time');

        $dateFrom = request('time_from');
        $dateTo = request('time_to');
        $phone_number = request('phone_number');
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = null;
        $date2 = null;

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $search = request('search');
        $descending = filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $is_export = filter_var(request('is_export'), FILTER_VALIDATE_BOOLEAN);


        $field_by = request('field_by') ?: null;
        $field_by_value = request('field_by_value') ?: null;

        if ($field_by == "order_status_code") {
            $field_by = "order_status";
            $field_by_value  = StatusDefineCode::getOrderStatusNum($field_by_value);
        }

        if ($field_by == "payment_status_code") {
            $field_by = "payment_status";
            $field_by_value  = StatusDefineCode::getPaymentStatusNum($field_by_value);
        }

        $order_status = StatusDefineCode::getOrderStatusNum(request("order_status_code"));
        $payment_status = StatusDefineCode::getPaymentStatusNum(request("payment_status_code"));

        $orders = Order::where(
            'orders.store_id',
            $request->store->id
        )
            // ->when(
            //     Order::isColumnValid($sortColumn = request('sort_by')),
            //     function ($query) use ($sortColumn, $descending) {
            //         $query->orderBy($sortColumn, $descending);
            //     }
            // )
            // ->when(empty(request('sort_by')) && empty($search), function ($query) {
            //     $query->orderBy('orders.created_at', 'desc');
            // })
            ->orderBy('orders.created_at', 'desc')
            // ->when($is_export, function ($query) {
            //     $query->leftJoin('staff', 'orders.created_by_staff_id', '=', 'staff.id')
            //         ->select('orders.*', 'staff.name as staff_name', 'staff.username as staff_username');
            // })
            ->when($is_export, function ($query) {
                $query->leftJoin('staff', 'orders.sale_by_staff_id', '=', 'staff.id')
                    ->select('orders.*', 'staff.name as staff_name', 'staff.username as staff_username');
            })
            ->when($phone_number  != null, function ($query) use ($phone_number) {
                $query->where('orders.phone_number', '=', $phone_number);
            })
            ->when(request('from_pos') != null, function ($query) {
                $query->where('from_pos', filter_var(request('from_pos'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when(request('agency_by_customer_id') != null, function ($query) {
                $query->where('agency_by_customer_id', request('agency_by_customer_id'));
            })
            ->when(count($order_from_list) > 0, function ($query) use ($order_from_list) {
                $query->whereIn('order_from', $order_from_list);
            })
            ->when(count($branch_id_list) > 0, function ($query) use ($branch_id_list) {
                $query->whereIn('orders.branch_id', $branch_id_list);
            })
            ->when(count($order_status_list) > 0, function ($query) use ($order_status_list) {
                $query->whereIn('order_status', $order_status_list);
            })
            ->when(count($payment_status_list) > 0, function ($query) use ($payment_status_list) {
                $query->whereIn('payment_status', $payment_status_list);
            })

            ->when(count($order_status_code_list2) > 0, function ($query) use ($order_status_code_list2) {
                $query->whereIn('order_status', $order_status_code_list2);
            })
            ->when(count($payment_status_code_list2) > 0, function ($query) use ($payment_status_code_list2) {
                $query->whereIn('payment_status', $payment_status_code_list2);
            })

            ->when(request('created_by_staff_id') != null, function ($query) {
                $query->where('created_by_staff_id', request('created_by_staff_id'));
            })
            ->when(request('sale_staff_id') != null, function ($query) {
                $query->where('orders.sale_by_staff_id', request('sale_staff_id'));
            })
            ->when(request('branch_id') != null, function ($query) {
                $query->where('orders.branch_id', request('branch_id'));
            })
            ->when(request('collaborator_by_customer_id') != null, function ($query) {
                $query->where('collaborator_by_customer_id', request('collaborator_by_customer_id'));
            })

            ->when(request('agency_ctv_by_customer_id') != null, function ($query) {
                $query->where('agency_ctv_by_customer_id', request('agency_ctv_by_customer_id'));
            })


            ->when($field_by != null && $field_by_value != null, function ($query) use ($field_by, $field_by_value) {
                $query->where($field_by, $field_by_value);
            })
            ->when($order_status !== null, function ($query) use ($order_status) {
                $query->where('orders.order_status', $order_status);
            })
            ->when($payment_status !== null, function ($query) use ($payment_status) {
                $query->where('orders.payment_status', $payment_status);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom,  $type_query_time) {
                if ($type_query_time == 'last_time_change_order_status') {
                    $query->where('orders.last_time_change_order_status', '>=', $dateFrom);
                } else {
                    $query->where('orders.created_at', '>=', $dateFrom);
                }
            })
            ->when($dateTo != null, function ($query) use ($dateTo,  $type_query_time) {
                if ($type_query_time == 'last_time_change_order_status') {
                    $query->where('orders.last_time_change_order_status', '<=', $dateTo);
                } else {
                    $query->where('orders.created_at', '<=', $dateTo);
                }
            })
            ->search($search, null, true, true);

        $total_price = $orders->sum('total_final');

        $custom = collect(
            [
                'total_price' => $total_price
            ]
        );

        $orders = $orders->paginate($is_export == true ? 10000 : (request('limit') == null ? 20 : request('limit')));

        $data = $custom->merge($orders);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }

    /**
     * Lịch sử trạng thái đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  order_id required order_id. Example: kds
     */
    public function status_records(Request $request)
    {

        $orderRecords = OrderRecord::where('store_id', $request->store->id)
            ->where('order_id', $request->order_id)
            ->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $orderRecords
        ], 200);
    }

    /**
     * Lấy thông tin 1 đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @urlParam  order_code required order_code. Example: order_code
     */
    public function getOne(Request $request, $id)
    {

        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $request->order_code)
            ->with('line_items')
            ->first();

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        PointCustomerUtils::bonus_point_from_order($request, $orderExists);
        $orderShipCode =  OrderShiperCode::where('order_id', $orderExists->id)
            ->where('partner_id',  $orderExists->partner_shipper_id)->first();
        $staffOrder = Staff::where('store_id', $request->store->id)
            ->where('id', $orderExists->created_by_staff_id)->orWhere('id', $orderExists->sale_by_staff_id)
            ->select('name')
            ->first();

        $orderExists->sent_delivery =  $orderShipCode != null ? true : false;
        $orderExists->order_ship_code =  $orderShipCode;
        $orderExists->staff_name =  $staffOrder ? $staffOrder->name : null;

        $agency_direct = null;
        $agency_indirect = null;
        $collaborator_direct = null;
        $collaborator_indirect = null;

        if ($orderExists->agency_ctv_by_customer_id) {
            if (AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_id)) {
                $agency_direct = Customer::where('store_id', $request->store->id)
                    ->where('id', $orderExists->agency_ctv_by_customer_id)
                    ->select('id', 'name')
                    ->first();
            }
        }

        if ($orderExists->agency_ctv_by_customer_referral_id) {
            if (AgencyUtils::isAgencyByCustomerId($orderExists->agency_ctv_by_customer_referral_id)) {
                $agency_indirect = Customer::where('store_id', $request->store->id)
                    ->where('id', $orderExists->agency_ctv_by_customer_referral_id)
                    ->select('id', 'name')
                    ->first();
            }
        }

        if ($orderExists->collaborator_by_customer_id) {
            if (CollaboratorUtils::isCollaborator($orderExists->collaborator_by_customer_id, $request->store->id)) {
                $collaborator_direct = Customer::where('store_id', $request->store->id)
                    ->where('id', $orderExists->collaborator_by_customer_id)
                    ->select('id', 'name')
                    ->first();
            }
        }

        if ($orderExists->collaborator_by_customer_referral_id) {
            if (CollaboratorUtils::isCollaborator($orderExists->collaborator_by_customer_referral_id, $request->store->id)) {
                $collaborator_indirect = Customer::where('store_id', $request->store->id)
                    ->where('id', $orderExists->collaborator_by_customer_referral_id)
                    ->select('id', 'name')
                    ->first();
            }
        }

        $orderExists->agency_direct = $agency_direct;
        $orderExists->agency_indirect = $agency_indirect;
        $orderExists->collaborator_direct = $collaborator_direct;
        $orderExists->collaborator_indirect = $collaborator_indirect;


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $orderExists
        ], 200);
    }

    static function sub_inventory($orderExists)
    {
        if (Cache::lock('sub_inventory' .  $orderExists->order_code, 1)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã xử lý",
            ], 400);
        }

        $num_status =  $orderExists->order_status;

        //Trừ số lượng sp trong kho
        if ($num_status ==  StatusDefineCode::COMPLETED || $num_status ==  StatusDefineCode::SHIPPING || $num_status ==  StatusDefineCode::RECEIVED_PRODUCT || $num_status == StatusDefineCode::PACKING) {
            foreach ($orderExists->line_items as $line_item) {

                if ($line_item->has_subtract_inventory == false) { //Kiểm tra trừ số lượng chưa
                    $line_item->update([
                        "has_subtract_inventory" => true
                    ]);

                    if (!empty($line_item->product_id) && !empty($line_item->branch_id)) {
                        InventoryUtils::add_sub_stock_by_id(
                            $line_item->store_id,
                            $line_item->branch_id,
                            $line_item->product_id,
                            $line_item->element_distribute_id,
                            $line_item->sub_element_distribute_id,
                            - ($line_item->quantity),
                            InventoryUtils::TYPE_EXPORT_ORDER_STOCK,
                            $orderExists->id,
                            $orderExists->order_code
                        );


                        InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($line_item->store_id,  $line_item->product_id);
                    }
                }  ///Trừ xong tồn kho

            }
        }
    }

    /**
     * Thay đổi trạng thái đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  order_code required Mã đơn hàng. Example: 1
     * @bodyParam order_status_code string required Mã trạng thái đơn hàng
     */
    public function change_order_status(Request $request)
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

        if ($request->order_status_code == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ORDER_STATUS_IS_REQUIRED[0],
                'msg' => MsgCode::ORDER_STATUS_IS_REQUIRED[1],
            ], 400);
        }


        if (StatusDefineCode::getOrderStatusNum($request->order_status_code) === null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_ORDER_STATUS_CODE[0],
                'msg' => MsgCode::INVALID_ORDER_STATUS_CODE[1],
            ], 400);
        }



        if (StatusDefineCode::getOrderStatusNum($request->order_status_code) == $orderExists->order_status) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đã ở trạng thái này",
            ], 400);
        }


        if ($request->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::WAITING_FOR_PROGRESSING)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn đã xử lý không thể chuyển về trạng thái xử lý",
            ], 400);
        }

        if (
            $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::OUT_OF_STOCK)
            ||
            $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::USER_CANCELLED)
            ||
            $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::CUSTOMER_CANCELLED)
            ||
            $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::DELIVERY_ERROR)
            ||
            $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::CUSTOMER_RETURNING)
        ) {

            if ($request->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::COMPLETED)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng đã hủy không thể hoàn thành hãy tạo đơn mới",
                ], 400);
            }
        }

        if (
            $request->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::PACKING)
            &&
            $request->order_status == StatusDefineCode::SHIPPING
            &&
            $request->order_status == StatusDefineCode::RECEIVED_PRODUCT
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đang giao không thể quay về trạng thái chuẩn bị hàng",
            ], 400);
        }


        if (
            $request->order_status != StatusDefineCode::getOrderStatusNum($request->order_status_code)
            &&
            $request->order_status == StatusDefineCode::COMPLETED
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đã hoàn thành không thể thay đổi trạng thái",
            ], 400);
        }


        $statusBefore =  StatusDefineCode::getOrderStatusCode($orderExists->order_status, true);
        $statusNew = StatusDefineCode::getOrderStatusNum($request->order_status_code, true);


        $num_status = StatusDefineCode::getOrderStatusNum($request->order_status_code);


        if ($statusBefore != $statusNew) {
            //
            PushNotificationCustomerJob::dispatch(
                $request->store->id,
                $orderExists->customer_id,
                "Đơn hàng " . $request->order_code,
                $statusNew,
                TypeFCM::ORDER_STATUS . '-' . $request->order_status_code,
                $request->order_code
            );

            if ($orderExists->customer_id != null) {
                StatusDefineCode::saveOrderStatus(
                    $request->store->id,
                    $orderExists->customer_id,
                    $orderExists->id,
                    "Trạng thái đơn hàng chuyển sang " . $statusNew,
                    0,
                    true,
                    $num_status,
                );
            }
        }

        $orderExists->update(
            [
                "order_status" => $num_status,
                "remaining_amount" => $num_status == StatusDefineCode::COMPLETED ? 0 : $orderExists->remaining_amount,
                "payment_status" => $num_status == 10 ? 2 : $orderExists->payment_status
            ]
        );

        PointCustomerUtils::bonus_point_from_order($request, $orderExists);
        RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
        RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
        RefundUtitls::auto_refund_point_for_customer($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);

        OrderController::sub_inventory($orderExists);
        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);

        if ($num_status ==  StatusDefineCode::COMPLETED) { // Nếu trạng thái chuyển sang đã hoàn thành và đã thanh toán
            PointCustomerUtils::bonus_point_for_agency_product_from_order($request, $orderExists);

            $orderExists->update(
                [
                    "completed_at" => Helper::getTimeNowDateTime()
                ]
            );
        }

        if ($num_status == StatusDefineCode::USER_CANCELLED) { // Nếu trạng thái chuyển sang đã hủy 
            SaveOperationHistoryUtils::save(
                $request,
                TypeAction::OPERATION_ACTION_CANCEL,
                TypeAction::FUNCTION_TYPE_ORDER,
                "Huỷ đơn hàng " . $orderExists->order_code,
                $orderExists->id,
                $orderExists->order_code
            );
        }

        // Nếu trạng thái chuyển sang đã trả hàng
        if ($num_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
            $refund = (new PosController)->refund($request, true);
        }

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Thay đổi trạng thái nhiều đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam  order_code required Mã đơn hàng. Example: 1
     * @bodyParam order_status_code string required Mã trạng thái đơn hàng
     */
    public function change_list_order_status(Request $request)
    {
        if ($request->order_status_code == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ORDER_STATUS_IS_REQUIRED[0],
                'msg' => MsgCode::ORDER_STATUS_IS_REQUIRED[1],
            ], 400);
        }

        if (!$request->order_codes || !is_array($request->order_codes)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ORDER_CODE_IS_REQUIRED[0],
                'msg' => MsgCode::ORDER_CODE_IS_REQUIRED[1],
            ], 400);
        }

        foreach ($request->order_codes as $order_code) {
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
            }

            if ($request->order_status_code == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ORDER_STATUS_IS_REQUIRED[0],
                    'msg' => MsgCode::ORDER_STATUS_IS_REQUIRED[1],
                ], 400);
            }


            if (StatusDefineCode::getOrderStatusNum($request->order_status_code) === null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_ORDER_STATUS_CODE[0],
                    'msg' => MsgCode::INVALID_ORDER_STATUS_CODE[1],
                ], 400);
            }



            if (StatusDefineCode::getOrderStatusNum($request->order_status_code) == $orderExists->order_status) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng đã ở trạng thái này",
                ], 400);
            }


            if ($request->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::WAITING_FOR_PROGRESSING)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn đã xử lý không thể chuyển về trạng thái xử lý",
                ], 400);
            }

            if (
                $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::OUT_OF_STOCK)
                ||
                $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::USER_CANCELLED)
                ||
                $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::CUSTOMER_CANCELLED)
                ||
                $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::DELIVERY_ERROR)
                ||
                $orderExists->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::CUSTOMER_RETURNING)
            ) {

                if ($request->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::COMPLETED)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::ERROR[0],
                        'msg' => "Đơn hàng đã hủy không thể hoàn thành hãy tạo đơn mới",
                    ], 400);
                }
            }

            if (
                $request->order_status_code == StatusDefineCode::getOrderStatusCode(StatusDefineCode::PACKING)
                &&
                $request->order_status == StatusDefineCode::SHIPPING
                &&
                $request->order_status == StatusDefineCode::RECEIVED_PRODUCT
            ) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng đang giao không thể quay về trạng thái chuẩn bị hàng",
                ], 400);
            }


            if (
                $request->order_status != StatusDefineCode::getOrderStatusNum($request->order_status_code)
                &&
                $request->order_status == StatusDefineCode::COMPLETED
            ) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng đã hoàn thành không thể thay đổi trạng thái",
                ], 400);
            }
        }

        foreach ($request->order_codes as $order_code) {
            $orderExists = Order::where('store_id', $request->store->id)
                ->where('order_code', $order_code)
                ->first();

            DB::transaction(function () use ($orderExists, $request) {
                $statusBefore =  StatusDefineCode::getOrderStatusCode($orderExists->order_status, true);
                $statusNew = StatusDefineCode::getOrderStatusNum($request->order_status_code, true);
                $num_status = StatusDefineCode::getOrderStatusNum($request->order_status_code);

                if ($statusBefore != $statusNew) {
                    //
                    PushNotificationCustomerJob::dispatch(
                        $request->store->id,
                        $orderExists->customer_id,
                        "Đơn hàng " . $request->order_code,
                        $statusNew,
                        TypeFCM::ORDER_STATUS . '-' . $request->order_status_code,
                        $request->order_code
                    );

                    if ($orderExists->customer_id != null) {
                        StatusDefineCode::saveOrderStatus(
                            $request->store->id,
                            $orderExists->customer_id,
                            $orderExists->id,
                            "Trạng thái đơn hàng chuyển sang " . $statusNew,
                            0,
                            true,
                            $num_status,
                        );
                    }
                }

                $orderExists->update(
                    [
                        "order_status" => $num_status,
                        "remaining_amount" => $num_status == StatusDefineCode::COMPLETED ? 0 : $orderExists->remaining_amount,
                        "payment_status" => $num_status == 10 ? 2 : $orderExists->payment_status
                    ]
                );

                PointCustomerUtils::bonus_point_from_order($request, $orderExists);
                RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
                RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
                RefundUtitls::auto_refund_money_for_ctv($orderExists, $request);
                RefundUtitls::auto_refund_point_for_customer($orderExists, $request);
                RevenueExpenditureUtils::auto_add_revenue_order_refund($orderExists, $request);

                OrderController::sub_inventory($orderExists);
                CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);

                if ($num_status ==  StatusDefineCode::COMPLETED) { // Nếu trạng thái chuyển sang đã hoàn thành và đã thanh toán
                    PointCustomerUtils::bonus_point_for_agency_product_from_order($request, $orderExists);
                }

                if ($num_status == StatusDefineCode::USER_CANCELLED) { // Nếu trạng thái chuyển sang đã hủy 
                    SaveOperationHistoryUtils::save(
                        $request,
                        TypeAction::OPERATION_ACTION_CANCEL,
                        TypeAction::FUNCTION_TYPE_ORDER,
                        "Huỷ đơn hàng " . $orderExists->order_code,
                        $orderExists->id,
                        $orderExists->order_code
                    );
                }

                // Nếu trạng thái chuyển sang đã trả hàng
                if ($num_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                    $refund = (new PosController)->refund($request, true);
                }

                SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);
            });
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }



    /**
     * Thanh toán đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam order_code required Mã đơn hàng. Example: 1
     * @bodyParam amount_money double số tiền thanh toán  (truyền lên đầy đủ sẽ tự động thanh toán, truyền 1 phần tự động chuyển thanh trạng thái thanh toán 1 phần, truyền 0 chưa thanh toán)
     * @bodyParam payment_method int phương thức thanh toán
     * 
     */
    public function pay_order(Request $request)
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

        if ($request->amount_money == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_VALUE[0],
                'msg' => MsgCode::INVALID_VALUE[1],
            ], 400);
        }

        if ($request->amount_money > $orderExists->remaining_amount) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PAYMENT_AMOUNT_CANNOT_BE_GREATER_THAN_THE_REMAINING_AMOUNT[0],
                'msg' => MsgCode::PAYMENT_AMOUNT_CANNOT_BE_GREATER_THAN_THE_REMAINING_AMOUNT[1],
            ], 400);
        }



        $paid = 0;
        $payment_status = 0;

        if ($request->amount_money >= $orderExists->remaining_amount) {
            $paid = $request->amount_money;
            $payment_status = StatusDefineCode::PAID;
        } else if ($request->amount_money < $orderExists->remaining_amount) {
            $paid = $request->amount_money;
            $payment_status = StatusDefineCode::PARTIALLY_PAID;
        }

        $order_status = null;
        if ($payment_status == StatusDefineCode::PAID && ($orderExists->order_from == Order::ORDER_FROM_POS_IN_STORE)) {
            $order_status = StatusDefineCode::COMPLETED;
        }

        if ($orderExists->customer_id != null) {
            StatusDefineCode::saveOrderStatus(
                $request->store->id,
                $orderExists->customer_id,
                $orderExists->id,
                "Thanh toán " . $request->amount_money,
                0,
                true,
                null
            );
        }

        $ship_discount_amount =  $orderExists->ship_discount_amount ?? 0;
        $cod = $orderExists->cod - $paid > 0 ? $orderExists->cod - $paid : 0;

        if ($ship_discount_amount > 0) {
            $cod = $orderExists->remaining_amount - $paid;
        }

        $orderExists->update(
            [
                "payment_status" => $payment_status,
                'remaining_amount' =>   $orderExists->remaining_amount - $paid,
                'cod' =>  $cod,
                "order_status" => $order_status == null ? $orderExists->order_status :  $order_status
            ]

        );

        RevenueExpenditureUtils::auto_add_expenditure_order($orderExists, $request);
        RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);

        PointCustomerUtils::bonus_point_from_order($request, $orderExists);
        OrderController::sub_inventory($orderExists);
        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Lich su thay đổi trạng thái thanh toán
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam order_code required Mã đơn hàng. Example: 1
     * @bodyParam payment_status_code string required Mã trạng thái thanh toán
     */
    function history_pay_order(Request $request)
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


        $history = HistoryPayOrder::where('order_id', $orderExists->id)
            ->orderBy('created_at', 'desc')
            ->get();

        OrderController::sub_inventory($orderExists);



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $history
        ], 200);
    }

    /**
     * Thay đổi trạng thái thanh toán
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam order_code required Mã đơn hàng. Example: 1
     * @bodyParam payment_status_code string required Mã trạng thái thanh toán
     */
    public function change_payment_status(Request $request)
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

        if ($request->payment_status_code == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PAYMENT_STATUS_IS_REQUIRED[0],
                'msg' => MsgCode::PAYMENT_STATUS_IS_REQUIRED[1],
            ], 400);
        }


        if (StatusDefineCode::getPaymentStatusNum($request->payment_status_code) === null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PAYMENT_STATUS_CODE[0],
                'msg' => MsgCode::INVALID_PAYMENT_STATUS_CODE[1],
            ], 400);
        }


        $statusBefore =  StatusDefineCode::getPaymentStatusCode($orderExists->payment_status, true);
        $statusNew = StatusDefineCode::getPaymentStatusNum($request->payment_status_code, true);

        if ($statusBefore  ==   $statusNew) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đã ở trạng thái này",
            ], 400);
        }

        if ($statusBefore == StatusDefineCode::PARTIALLY_PAID) {
            if ($statusNew == StatusDefineCode::UNPAID || $statusNew == StatusDefineCode::PAY_REFUNDS || $statusNew == StatusDefineCode::WAITING_FOR_PROGRESSING_PAYMENT) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng đã thanh toán một phần",
                ], 400);
            }
        }


        if ($statusBefore == StatusDefineCode::PAID) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đã thanh toán hết",
            ], 400);
        }


        if ($statusBefore == StatusDefineCode::PAY_REFUNDS) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Đơn hàng đã bị hoàn trả",
            ], 400);
        }


        if ($statusBefore != $statusNew) {

            PointCustomerUtils::bonus_point_from_order($request, $orderExists);

            if ($orderExists->customer_id != null) {
                StatusDefineCode::saveOrderStatus(
                    $request->store->id,
                    $orderExists->customer_id,
                    $orderExists->id,
                    "Trạng thái thanh toán chuyển sang " . $statusNew,
                    0,
                    true,
                    null
                );
            }
        }




        $orderExists->update(
            [
                "payment_status" => StatusDefineCode::getPaymentStatusNum($request->payment_status_code),
            ]
        );

        if ($orderExists->payment_status == StatusDefineCode::PAID) {
            $orderExists->update(
                [
                    "remaining_amount" => 0,
                    // 'cod' => $orderExists->remaining_amount > 0 ? $orderExists->total_shipping_fee : 0
                    'cod' =>  0
                ]
            );

            $orderExists->remaining_amount = 0;
        }


        RevenueExpenditureUtils::auto_add_revenue_order($orderExists, $request);
        CollaboratorUtils::handelBalanceAgencyAndCollaborator($request, $orderExists);
        PointCustomerUtils::bonus_point_from_order($request, $orderExists);

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Cập nhật thông tin 1 đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam partner_shipper_id required Phương thức vận chuyển
     * @bodyParam ship_speed_code required Mã vận chuyển
     * @bodyParam total_shipping_fee required Số tiền ship
     * @bodyParam branch_id required Id chi nhánh
     * 
     */
    public function update(Request $request)
    {
        $shipSpeedCode = null;
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

        if (!empty($request->branch_id)) {
            $branch_id = $request->branch_id;
            $branchExists = Branch::where('store_id', $request->store->id)
                ->where('id', $branch_id)
                ->first();
            if ($branchExists == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
                    'msg' => MsgCode::NO_BRANCH_EXISTS[1],
                ], 400);
            }

            if ($orderExists->branch_id !=  $request->branch_id && $orderExists->order_status == StatusDefineCode::COMPLETED) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Đơn hàng đã xử lý không thể điều chuyển chi nhánh",
                ], 400);
            }

            foreach ($orderExists->line_items as $line_item) {
                $line_item->update([
                    'branch_id' => $request->branch_id
                ]);
            }
        }

        $total_final =   $orderExists->total_final;
        $total_shipping_fee =   $orderExists->total_shipping_fee;
        $shipSpeedCode = $orderExists->ship_speed_code;
        $total_shipping_fee_request = $request->total_shipping_fee;

        $ship_discount_amount =  $orderExists->ship_discount_amount ?? 0;
        // if ($request->total_shipping_fee  != $orderExists->total_shipping_fee) {
        //     $ship_discount_amount = 0;
        // }
        if ($ship_discount_amount > 0) {
            $total_shipping_fee_request = $ship_discount_amount - $request->total_shipping_fee >= 0 ? 0 : $request->total_shipping_fee - $ship_discount_amount;
        }

        $remaining_amount = $orderExists->remaining_amount;
        if ($request->total_shipping_fee !== null) {

            if ($total_shipping_fee_request >= $orderExists->total_shipping_fee) {
                $remaining_amount  = $orderExists->remaining_amount + ($total_shipping_fee_request - $orderExists->total_shipping_fee);
            }

            if ($total_shipping_fee_request < $orderExists->total_shipping_fee) {
                $remaining_amount  = $orderExists->remaining_amount - ($orderExists->total_shipping_fee - $total_shipping_fee_request);
            }

            $total_final =  $total_final - $orderExists->total_shipping_fee +  $total_shipping_fee_request;
        }

        // Xử lý vận chuyển
        try {
            if ($request->ship_speed_code) {
                $shipSpeedCode = $request->ship_speed_code;
            } else if (!empty($request->partner_shipper_id)) {
                $shipSpeedCode = config('saha.shipper.list_shipper')[$request->partner_shipper_id]["ship_speed_code_default"];
            }
        } catch (\Throwable $th) {
        }


        $orderExists->update(
            Helper::sahaRemoveItemArrayIfNullValue(
                [
                    "partner_shipper_id" => $request->partner_shipper_id,
                    'ship_speed_code' => $shipSpeedCode,
                    // 'ship_discount_amount' => 0,
                    'total_shipping_fee' =>  $total_shipping_fee_request,
                    "branch_id" => $request->branch_id,
                    'remaining_amount' => $remaining_amount,
                    "total_final" => $total_final,
                    "cod" => $ship_discount_amount > 0 ? $remaining_amount : null
                ]
            )
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_ORDER,
            "Sửa đơn hàng " . $orderExists->order_code,
            $orderExists->id,
            $orderExists->order_code
        );
        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::UPDATE_ORDER,   $orderExists);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Thay đổi thông tin kiện hàng
     * 
     * @urlParam  store_code required Store code. Example: kds
     * 
     * @bodyParam package_weight required Khối lượng gram
     * @bodyParam package_length required Chiều dài
     * @bodyParam package_width required Chiều rộng
     * @bodyParam package_height required Chiều cao
     * 
     */
    public function updatePackage(Request $request)
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

        if (($request->cod < 0 || $request->cod == "") && $request->cod !== null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_COD[0],
                'msg' => MsgCode::INVALID_COD[1],
            ], 400);
        }

        $orderExists->update(
            [
                "package_weight" => $request->package_weight,
                'package_length' => $request->package_length,
                'package_width' =>  $request->package_width,
                "package_height" => $request->package_height,
                "cod" => $request->cod == null ? $orderExists->cod : $request->cod
            ]
        );


        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_UPDATE,
            TypeAction::FUNCTION_TYPE_ORDER,
            "Sửa thông tin kích thước khối lượng kiện hàng " . $orderExists->order_code,
            $orderExists->id,
            $orderExists->order_code
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $orderExists
        ], 200);
    }


    /**
     * Xóa 1 đơn hàng
     * @urlParam  store_code required Store code. Example: kds
     * 
     */
    public function delete(Request $request)
    {

        $order_code = $request->route()->parameter('order_code');
        $tempOrder = null;

        $orderExists = Order::where('store_id', $request->store->id)
            ->where('order_code', $order_code)
            ->first();
        $tempOrder = clone $orderExists;

        if ($orderExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ], 404);
        }

        $orderExists->delete();

        SaveOperationHistoryUtils::save(
            $request,
            TypeAction::OPERATION_ACTION_DELETE,
            TypeAction::FUNCTION_TYPE_ORDER,
            "Xóa đơn hàng " . $tempOrder->order_code,
            $tempOrder->id,
            $tempOrder->order_code
        );

        SendToWebHookUtils::sendToWebHook(
            $request,
            SendToWebHookUtils::DELETE_ORDER,
            [
                'order_code' =>  $order_code
            ]
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function handleGetTotalPriceByMethodPayment(Request $request)
    {
        $methodPaymentId = $request->method_payment_id;

        $order_from_list = request("order_from_list") == null ? [] : explode(',', str_replace(" ", "", request("order_from_list")));
        $branch_id_list = request("branch_id_list") == null ? [] : explode(',',  str_replace(" ", "", request("branch_id_list")));

        $order_status_list = request("order_status_list") == null ? [] : explode(',',  str_replace(" ", "", request("order_status_list")));
        $payment_status_list = request("payment_status_list") == null ? [] : explode(',',  str_replace(" ", "", request("payment_status_list")));

        $order_status_code_list = request("order_status_code_list") == null ? [] : explode(',',  str_replace(" ", "", request("order_status_code_list")));
        $payment_status_code_list = request("payment_status_code_list") == null ? [] : explode(',',  str_replace(" ", "", request("payment_status_code_list")));

        $order_status_code_list2 = [];
        $payment_status_code_list2 = [];

        foreach ($order_status_code_list as $item) {
            array_push($order_status_code_list2,  StatusDefineCode::getOrderStatusNum($item));
        }
        foreach ($payment_status_code_list as $item) {
            array_push($payment_status_code_list2,  StatusDefineCode::getPaymentStatusNum($item));
        }


        $type_query_time = request('type_query_time');

        $dateFrom = request('time_from');
        $dateTo = request('time_to');
        $phone_number = request('phone_number');
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = null;
        $date2 = null;

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $search = request('search');
        $descending = filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $is_export = filter_var(request('is_export'), FILTER_VALIDATE_BOOLEAN);


        $field_by = request('field_by') ?: null;
        $field_by_value = request('field_by_value') ?: null;

        if ($field_by == "order_status_code") {
            $field_by = "order_status";
            $field_by_value  = StatusDefineCode::getOrderStatusNum($field_by_value);
        }

        if ($field_by == "payment_status_code") {
            $field_by = "payment_status";
            $field_by_value  = StatusDefineCode::getPaymentStatusNum($field_by_value);
        }

        $order_status = StatusDefineCode::getOrderStatusNum(request("order_status_code"));
        $payment_status = StatusDefineCode::getPaymentStatusNum(request("payment_status_code"));

        $orders = Order::where(
            'orders.store_id',
            $request->store->id
        )->where('orders.payment_status', 2)
            ->orderBy('orders.created_at', 'desc')
            ->when($is_export, function ($query) {
                $query->leftJoin('staff', 'orders.created_by_staff_id', '=', 'staff.id')
                    ->select('orders.*', 'staff.name as staff_name', 'staff.username as staff_username');
            })
            ->when($phone_number  != null, function ($query) use ($phone_number) {
                $query->where('orders.phone_number', '=', $phone_number);
            })
            ->when(request('from_pos') != null, function ($query) {
                $query->where('from_pos', filter_var(request('from_pos'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when(request('agency_by_customer_id') != null, function ($query) {
                $query->where('agency_by_customer_id', request('agency_by_customer_id'));
            })
            ->when(count($order_from_list) > 0, function ($query) use ($order_from_list) {
                $query->whereIn('order_from', $order_from_list);
            })
            ->when(count($branch_id_list) > 0, function ($query) use ($branch_id_list) {
                $query->whereIn('orders.branch_id', $branch_id_list);
            })
            ->when(count($order_status_list) > 0, function ($query) use ($order_status_list) {
                $query->whereIn('order_status', $order_status_list);
            })
            ->when(count($payment_status_list) > 0, function ($query) use ($payment_status_list) {
                $query->whereIn('payment_status', $payment_status_list);
            })

            ->when(count($order_status_code_list2) > 0, function ($query) use ($order_status_code_list2) {
                $query->whereIn('order_status', $order_status_code_list2);
            })
            ->when(count($payment_status_code_list2) > 0, function ($query) use ($payment_status_code_list2) {
                $query->whereIn('payment_status', $payment_status_code_list2);
            })

            ->when(request('created_by_staff_id') != null, function ($query) {
                $query->where('created_by_staff_id', request('created_by_staff_id'));
            })
            ->when(request('sale_staff_id') != null, function ($query) {
                $query->where('orders.sale_by_staff_id', request('sale_staff_id'));
            })
            ->when(request('branch_id') != null, function ($query) {
                $query->where('orders.branch_id', request('branch_id'));
            })
            ->when(request('collaborator_by_customer_id') != null, function ($query) {
                $query->where('collaborator_by_customer_id', request('collaborator_by_customer_id'));
            })

            ->when(request('agency_ctv_by_customer_id') != null, function ($query) {
                $query->where('agency_ctv_by_customer_id', request('agency_ctv_by_customer_id'));
            })


            ->when($field_by != null && $field_by_value != null, function ($query) use ($field_by, $field_by_value) {
                $query->where($field_by, $field_by_value);
            })
            ->when($order_status !== null, function ($query) use ($order_status) {
                $query->where('orders.order_status', $order_status);
            })
            ->when($payment_status !== null, function ($query) use ($payment_status) {
                $query->where('orders.payment_status', $payment_status);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom,  $type_query_time) {
                if ($type_query_time == 'last_time_change_order_status') {
                    $query->where('orders.last_time_change_order_status', '>=', $dateFrom);
                } else {
                    $query->where('orders.created_at', '>=', $dateFrom);
                }
            })
            ->when($dateTo != null, function ($query) use ($dateTo,  $type_query_time) {
                if ($type_query_time == 'last_time_change_order_status') {
                    $query->where('orders.last_time_change_order_status', '<=', $dateTo);
                } else {
                    $query->where('orders.created_at', '<=', $dateTo);
                }
            })
            ->when($methodPaymentId != null && $methodPaymentId != "undefined", function ($query) use ($methodPaymentId) {
                if ($methodPaymentId == "0") {
                    $query->whereIn('payment_partner_id', [0, 1]);
                } else if ($methodPaymentId == "cod") {
                    // $query->where('cod', '>', 0);
                    $query->where('cod', '>', 0)->whereIn('payment_partner_id', [0, 1]);
                } else {
                    $query->where('payment_partner_id', $methodPaymentId);
                }
            })


            ->search($search, null, true, true);

        $total_price = $orders->sum('total_final');

        $custom = collect(
            [
                'total_price' => $total_price
            ]
        );

        $orders = $orders->paginate($is_export == true ? 10000 : (request('limit') == null ? 20 : request('limit')));

        $data = $custom->merge($orders);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ], 200);
    }

    public function handleGetReportPaymentByProduct(Request $request)
    {
        $methodPaymentId = $request->method_payment_id;

        $order_from_list = request("order_from_list") == null ? [] : explode(',', str_replace(" ", "", request("order_from_list")));
        $branch_id_list = request("branch_id_list") == null ? [] : explode(',',  str_replace(" ", "", request("branch_id_list")));

        $order_status_list = request("order_status_list") == null ? [] : explode(',',  str_replace(" ", "", request("order_status_list")));
        $payment_status_list = request("payment_status_list") == null ? [] : explode(',',  str_replace(" ", "", request("payment_status_list")));

        $order_status_code_list = request("order_status_code_list") == null ? [] : explode(',',  str_replace(" ", "", request("order_status_code_list")));
        $payment_status_code_list = request("payment_status_code_list") == null ? [] : explode(',',  str_replace(" ", "", request("payment_status_code_list")));

        $order_status_code_list2 = [];
        $payment_status_code_list2 = [];

        foreach ($order_status_code_list as $item) {
            array_push($order_status_code_list2,  StatusDefineCode::getOrderStatusNum($item));
        }
        foreach ($payment_status_code_list as $item) {
            array_push($payment_status_code_list2,  StatusDefineCode::getPaymentStatusNum($item));
        }


        $type_query_time = request('type_query_time');

        $dateFrom = request('time_from');
        $dateTo = request('time_to');
        $phone_number = request('phone_number');
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = null;
        $date2 = null;

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $search = request('search');
        $descending = filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $is_export = filter_var(request('is_export'), FILTER_VALIDATE_BOOLEAN);


        $field_by = request('field_by') ?: null;
        $field_by_value = request('field_by_value') ?: null;

        if ($field_by == "order_status_code") {
            $field_by = "order_status";
            $field_by_value  = StatusDefineCode::getOrderStatusNum($field_by_value);
        }

        if ($field_by == "payment_status_code") {
            $field_by = "payment_status";
            $field_by_value  = StatusDefineCode::getPaymentStatusNum($field_by_value);
        }

        $order_status = StatusDefineCode::getOrderStatusNum(request("order_status_code"));
        $payment_status = StatusDefineCode::getPaymentStatusNum(request("payment_status_code"));

        $orders = Order::where(
            'orders.store_id',
            $request->store->id
        )->where("orders.payment_status", 2)
            ->orderBy('orders.created_at', 'desc')
            ->when($is_export, function ($query) {
                $query->leftJoin('staff', 'orders.created_by_staff_id', '=', 'staff.id')
                    ->select('orders.*', 'staff.name as staff_name', 'staff.username as staff_username');
            })
            ->when($phone_number  != null, function ($query) use ($phone_number) {
                $query->where('orders.phone_number', '=', $phone_number);
            })
            ->when(request('from_pos') != null, function ($query) {
                $query->where('from_pos', filter_var(request('from_pos'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when(request('agency_by_customer_id') != null, function ($query) {
                $query->where('agency_by_customer_id', request('agency_by_customer_id'));
            })
            ->when(count($order_from_list) > 0, function ($query) use ($order_from_list) {
                $query->whereIn('order_from', $order_from_list);
            })
            ->when(count($branch_id_list) > 0, function ($query) use ($branch_id_list) {
                $query->whereIn('orders.branch_id', $branch_id_list);
            })
            ->when(count($order_status_list) > 0, function ($query) use ($order_status_list) {
                $query->whereIn('order_status', $order_status_list);
            })
            ->when(count($payment_status_list) > 0, function ($query) use ($payment_status_list) {
                $query->whereIn('payment_status', $payment_status_list);
            })

            ->when(count($order_status_code_list2) > 0, function ($query) use ($order_status_code_list2) {
                $query->whereIn('order_status', $order_status_code_list2);
            })
            ->when(count($payment_status_code_list2) > 0, function ($query) use ($payment_status_code_list2) {
                $query->whereIn('payment_status', $payment_status_code_list2);
            })

            ->when(request('created_by_staff_id') != null, function ($query) {
                $query->where('created_by_staff_id', request('created_by_staff_id'));
            })
            ->when(request('sale_staff_id') != null, function ($query) {
                $query->where('orders.sale_by_staff_id', request('sale_staff_id'));
            })
            ->when(request('branch_id') != null, function ($query) {
                $query->where('orders.branch_id', request('branch_id'));
            })
            ->when(request('collaborator_by_customer_id') != null, function ($query) {
                $query->where('collaborator_by_customer_id', request('collaborator_by_customer_id'));
            })

            ->when(request('agency_ctv_by_customer_id') != null, function ($query) {
                $query->where('agency_ctv_by_customer_id', request('agency_ctv_by_customer_id'));
            })


            ->when($field_by != null && $field_by_value != null, function ($query) use ($field_by, $field_by_value) {
                $query->where($field_by, $field_by_value);
            })
            ->when($order_status !== null, function ($query) use ($order_status) {
                $query->where('orders.order_status', $order_status);
            })
            ->when($payment_status !== null, function ($query) use ($payment_status) {
                $query->where('orders.payment_status', $payment_status);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom,  $type_query_time) {
                if ($type_query_time == 'last_time_change_order_status') {
                    $query->where('orders.last_time_change_order_status', '>=', $dateFrom);
                } else {
                    $query->where('orders.created_at', '>=', $dateFrom);
                }
            })
            ->when($dateTo != null, function ($query) use ($dateTo,  $type_query_time) {
                if ($type_query_time == 'last_time_change_order_status') {
                    $query->where('orders.last_time_change_order_status', '<=', $dateTo);
                } else {
                    $query->where('orders.created_at', '<=', $dateTo);
                }
            })
            ->when($methodPaymentId != null && $methodPaymentId != "undefined", function ($query) use ($methodPaymentId) {
                $query->where('payment_method_id', $methodPaymentId);
            });



        $perPage = $is_export ? 10000 : (request('limit') ?: 20);

        $data1 = $orders->get()->pluck('line_items_at_time')->flatten();
        $soldProducts = [];
        foreach ($data1 as $item) {
            $product = json_decode(json_encode($item), true);
            if ($search != "" && !Str::contains($product['name'], $search)) {
                continue;
            }

            if (array_key_exists($product['id'], $soldProducts)) {
                // Cộng thêm số lượng
                $soldProducts[$product['id']]['quantity'] += $product['quantity'];
            } else {
                // Nếu sản phẩm chưa tồn tại, thêm vào mảng
                $soldProducts[$product['id']] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                ];
            }
        }

        usort($soldProducts, function ($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        $page = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = array_slice($soldProducts, ($page - 1) * $perPage, $perPage);
        $paginatedData = new LengthAwarePaginator(
            $currentPageItems,
            count($soldProducts),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $paginatedData,
        ], 200);
    }
}
