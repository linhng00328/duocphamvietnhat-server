<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\RevenueExpenditureUtils;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HistoryPayImportStock;
use App\Models\HistoryPayOrder;
use App\Models\ImportStock;
use App\Models\MsgCode;
use App\Models\Product;
use App\Models\ImportStockItem;
use App\Models\ImportTimeHistory;
use App\Models\Order;
use App\Models\RevenueExpenditure;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Http\Request;


/**
 * @group  User/Phiếu thu chi
 * 
 * 
 * 
 * 
 */

class RevenueExpenditureController extends Controller
{

    function checkValid($request)
    {
        if ($request->change_money <=  0) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::VALUE_MUST_BE_GREATER_THAN_0[0],
                'msg' => MsgCode::VALUE_MUST_BE_GREATER_THAN_0[1],
            ], 400);
        }

        if (!in_array($request->recipient_group, RevenueExpenditureUtils::ARR_RECIPIENT_GROUP)) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_RECIPIENT_GROUP_EXISTS[0],
                'msg' => MsgCode::NO_RECIPIENT_GROUP_EXISTS[1],
            ], 400);
        }

        if (!in_array($request->payment_method,   RevenueExpenditureUtils::ARR_PAYMENT)) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PAYMENT_METHOD_EXISTS[0],
                'msg' => MsgCode::NO_PAYMENT_METHOD_EXISTS[1],
            ], 400);
        }

        if ($request->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER) {
            $customerExists = Customer::where('id', $request->recipient_references_id)
                ->where('store_id', $request->store->id)
                ->first();

            if (empty($customerExists)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CUSTOMER_EXISTS[0],
                    'msg' => MsgCode::NO_CUSTOMER_EXISTS[1],
                ], 404);
            }
        }
        if ($request->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_STAFF) {
            $staffExists = Staff::where('id', $request->recipient_references_id)
                ->where('store_id', $request->store->id)
                ->first();

            if (empty($staffExists)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                    'msg' => MsgCode::NO_STAFF_EXISTS[1],
                ], 404);
            }
        }
        if ($request->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER) {
            $supplierExists = Supplier::where('id', $request->recipient_references_id)
                ->where('store_id', $request->store->id)
                ->first();

            if (empty($supplierExists)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SUPPLIER_EXISTS[0],
                    'msg' => MsgCode::NO_SUPPLIER_EXISTS[1],
                ], 404);
            }
        }
        if ($request->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_OTHER) {
        }

        return null;
    }

    /**
     * Tạo phiếu thu chi
     * 
     * 
     * @urlParam  store_code required Store code
     * @bodyParam is_revenue boolean true phiếu thu, false phiếu chi
     * @bodyParam change_money double Số tiền
     * @bodyParam recipient_group int nhóm khách hàng 0 khách hàng, 1Nhóm nhà cung cấp, 2nhân viên,  3Đối tượng khác
     * @bodyParam recipient_references_id int ID đại diện chủ thể (khách hàng, Nhóm nhà cung cấp,nhân viên,Đối tượng khác)
     * @bodyParam allow_accounting boolean Cho phép hạch toán
     * @bodyParam description String Mô tả
     * @bodyParam code String Mã phiếu
     * @bodyParam type int Kiểu phiếu
     * @bodyParam reference_name String Tham chiếu 
     * @bodyParam payment_method int kiểu thanh toán (0 tiền mặt, 1 quẹt thẻ, 2 cod, 3 chuyển khoản)
     */
    public function create(Request $request)
    {


        $checkValid = $this->checkValid($request);

        if ($checkValid  != null) {
            return   $checkValid;
        }


        //Thanh toán cho đơn hàng khách hàng
        if (
            $request->type == RevenueExpenditureUtils::TYPE_PAYMENT_ORDERS
            &&
            $request->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER
        ) {

            $customerExists = Customer::where('id', $request->recipient_references_id)
                ->where('store_id', $request->store->id)
                ->first();

            $sum_order_debt =   Order::where('store_id', $request->store->id)
                ->where('customer_id', $customerExists->id)
                ->where('remaining_amount', '>', 0)
                ->when($request->branch != null, function ($query) use ($request) {
                    $query->where(
                        'branch_id',
                        $request->branch->id
                    );
                })
                ->sum('remaining_amount');


            if ($request->change_money >    $sum_order_debt || $sum_order_debt  == 0) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PAYMENT_AMOUNT_CANNOT_GREATER_THAN_ORDER_DEBT[0],
                    'msg' => "Số tiền nợ đơn hàng hiện tại " . number_format($sum_order_debt),
                ], 400);
            }


            $current_money = $request->change_money;

            $order_remainings = Order::where('store_id', $request->store->id)
                ->where('customer_id', $customerExists->id)
                ->where('remaining_amount', '>', 0)
                ->when($request->branch != null, function ($query) use ($request) {
                    $query->where(
                        'branch_id',
                        $request->branch->id
                    );
                })
                ->orderBy('id', 'asc')
                ->get();


            $revenueExpenditureCreate = RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_PAYMENT_ORDERS,
                $request->recipient_group,
                $request->recipient_references_id,
                Helper::getRandomRevenueExpenditureString(),
                $request->references_id,
                $request->references_value,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_PAY_REVENUE,
                $request->change_money,
                true,
                "Tạo phiếu thu từ khách hàng trả nợ",
                $request->payment_method
            );

            foreach ($order_remainings  as $order) {

                if ($current_money > 0) {

                    if ($current_money >= $order->remaining_amount) {
                        $paid = $order->remaining_amount;
                        $payment_status = StatusDefineCode::PAID;
                    } else if ($current_money < $order->remaining_amount) {
                        $paid = $current_money;
                        $payment_status = StatusDefineCode::PARTIALLY_PAID;
                    }


                    HistoryPayOrder::create([
                        "store_id" => $request->store->id,
                        "order_id" => $order->id,
                        "payment_method_id" => $request->payment_method,
                        "money" => $paid ?? 0,
                        'remaining_amount' =>    $order->remaining_amount - $paid,
                        'revenue_expenditure_id' => $revenueExpenditureCreate->id
                    ]);

                    $order->update(
                        [
                            "payment_status" => $payment_status,
                            'remaining_amount' =>   $order->remaining_amount - $paid,
                            'order_status' => ($payment_status == StatusDefineCode::PAID) ? StatusDefineCode::COMPLETED : $order->order_status
                        ]
                    );

                    $current_money =  $current_money - $paid;
                }
            }
        }
        //Thanh toán cho đơn nhập hàng
        else  if (
            $request->type == RevenueExpenditureUtils::TYPE_PAYMENT_IMPORT_STOCK
            &&
            $request->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER
        ) {

            $supplierExists = Supplier::where('id', $request->recipient_references_id)
                ->where('store_id', $request->store->id)
                ->first();

            $sum_import_stock_debt =   ImportStock::where('store_id', $request->store->id)
                ->where('supplier_id', $supplierExists->id)
                ->where('remaining_amount', '>', 0)
                ->when($request->branch != null, function ($query) use ($request) {
                    $query->where(
                        'branch_id',
                        $request->branch->id
                    );
                })
                ->whereIn('status', [InventoryUtils::STATUS_IMPORT_STOCK_WAREHOUSE, InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED])
                ->whereIn('payment_status', [InventoryUtils::STATUS_IMPORT_STOCK_UNPAID, InventoryUtils::STATUS_IMPORT_STOCK_PART_PAYMENT])
                ->sum('remaining_amount');



            if (abs($request->change_money - $sum_import_stock_debt) <= 1) {
                $request->change_money = (int)$sum_import_stock_debt;
            }

            if ($sum_import_stock_debt  == 0) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PAYMENT_AMOUNT_CANNOT_GREATER_THAN_ORDER_DEBT[0],
                    'msg' => "Số tiền nợ đơn nhập hiện tại " . number_format($sum_import_stock_debt),
                ], 400);
            }


            $current_money = $request->change_money;

            $import_stock_remainings = ImportStock::where('store_id', $request->store->id)
                ->where('supplier_id', $supplierExists->id)
                ->where('remaining_amount', '>', 0)
                ->whereIn('status', [InventoryUtils::STATUS_IMPORT_STOCK_WAREHOUSE])
                ->when($request->branch != null, function ($query) use ($request) {
                    $query->where(
                        'branch_id',
                        $request->branch->id
                    );
                })
                ->whereIn('payment_status', [InventoryUtils::STATUS_IMPORT_STOCK_UNPAID, InventoryUtils::STATUS_IMPORT_STOCK_PART_PAYMENT])
                ->orderBy('id', 'asc')
                ->get();


            $revenueExpenditureCreate = RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                RevenueExpenditureUtils::TYPE_PAYMENT_IMPORT_STOCK,
                $request->recipient_group,
                $request->recipient_references_id,
                Helper::getRandomRevenueExpenditureString(),
                $request->references_id,
                $request->references_value,
                null,
                RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_DEBT_IMPORT_STOCK_EXPENDITURE,
                $request->change_money,
                false,
                "Tạo phiếu chi từ trả tiền nợ NCC",
                $request->payment_method
            );

            foreach ($import_stock_remainings  as $import_stock) {

                if ($current_money > 0) {

                    if ($current_money >= $import_stock->remaining_amount) {
                        $paid = $import_stock->remaining_amount;
                        $payment_status = InventoryUtils::STATUS_IMPORT_STOCK_PAID;
                    } else if ($current_money < $import_stock->remaining_amount) {
                        $paid = $current_money;
                        $payment_status = InventoryUtils::STATUS_IMPORT_STOCK_PART_PAYMENT;
                    }


                    HistoryPayImportStock::create([
                        "store_id" => $request->store->id,
                        "branch_id" => $request->branch->id,
                        "import_stock_id" => $import_stock->id,
                        "payment_method" => $request->payment_method,
                        "money" =>  $paid,
                        'revenue_expenditure_id' => $revenueExpenditureCreate->id,
                        "remaining_amount" => $import_stock->remaining_amount - $paid,
                    ]);

                    $import_stock->update(
                        Helper::sahaRemoveItemArrayIfNullValue(
                            [
                                "payment_status" => $payment_status,
                                'remaining_amount' =>   $import_stock->remaining_amount - $paid,
                                "status" => ($payment_status  ==  InventoryUtils::STATUS_IMPORT_STOCK_PAID) ? InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED : null
                            ]
                        )
                    );

                    ImportStockController::addHistoryImportStock(
                        $request,
                        InventoryUtils::STATUS_IMPORT_STOCK_COMPLETED,
                        $import_stock->id
                    );

                    $current_money =  $current_money - $paid;
                }
            }
        } else {

            $revenueExpenditureCreate  = RevenueExpenditureUtils::add_new_revenue_expenditure(
                $request,
                $request->type,
                $request->recipient_group,
                $request->recipient_references_id,
                Helper::getRandomRevenueExpenditureString(),
                $request->references_id,
                $request->references_value,
                $request->reference_name,
                $request->is_revenue == true ? RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_REVENUE : RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_EXPENDITURE,
                $request->change_money,
                $request->is_revenue,
                $request->description,
                $request->payment_method
            );
        }



        return $this->getOneData($revenueExpenditureCreate->id, $request);
    }

    /**
     * Danh sách phiếu thu chi
     * 
     * @urlParam  store_code required Store code
     * @queryParam recipient_group int id Nhóm khách hàng
     * @queryParam recipient_references_id int id ID chủ thể
     * @queryParam  search Mã phiếu
     * @queryParam is_revenue boolean Phải thu không
     * 
     */
    function getAll(Request $request)
    {
        $is_revenue = request('is_revenue');

        $recipient_group = request('recipient_group');
        $recipient_references_id = request('recipient_references_id');


        if (!in_array($recipient_group, RevenueExpenditureUtils::ARR_RECIPIENT_GROUP)) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_RECIPIENT_GROUP_EXISTS[0],
                'msg' => MsgCode::NO_RECIPIENT_GROUP_EXISTS[1],
            ], 400);
        }

        $search = request('search');
        $revenueExpenditures = RevenueExpenditure::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->when($recipient_group != null, function ($query) use ($recipient_group) {
                $query->where('recipient_group', '=', $recipient_group);
            })
            ->when($is_revenue  !== null, function ($query) use ($is_revenue) {
                $query->where('is_revenue', filter_var($is_revenue, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($recipient_references_id  !== null, function ($query) use ($recipient_references_id) {
                $query->where('recipient_references_id', '=', $recipient_references_id);
            })

            ->orderBy('id', 'desc')
            ->search($search)
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $revenueExpenditures
        ], 200);
    }

    /**
     * Thông tin phiếu thu chi
     * 
     * @urlParam  store_code required Store code
     * @urlParam  revenue_expenditure_id required Id phiếu thu chi
     */
    function getOne(Request $request)
    {

        $revenue_expenditure_id = $request->route()->parameter('revenue_expenditure_id');

        $revenue_expenditure = RevenueExpenditure::where('id', $revenue_expenditure_id)->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        if ($revenue_expenditure == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_REVENUE_EXPENDITURE_EXISTS[0],
                'msg' => MsgCode::NO_REVENUE_EXPENDITURE_EXISTS[1],
            ], 404);
        }

        return $this->getOneData($revenue_expenditure_id, $request);
    }


    function getOneData($revenue_expenditure_id, $request)
    {

        $history_pay_orders = HistoryPayOrder::where('revenue_expenditure_id',    $revenue_expenditure_id)
            ->orderBy('created_at', 'desc')
            ->get();
        $revenue_expenditure = RevenueExpenditure::where('id', $revenue_expenditure_id)
            ->where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)->first();

        foreach ($history_pay_orders  as  $history_pay_order) {
            $order = Order::where('id',  $history_pay_order->order_id)->first();
            $history_pay_order->order_id_ref =   $history_pay_order->order_id;
            $history_pay_order->order_code =   $order->order_code;
        }

        $revenue_expenditure->history_pay_orders = $history_pay_orders;


        $history_import_stocks = HistoryPayImportStock::where('revenue_expenditure_id',    $revenue_expenditure_id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($history_import_stocks  as  $history_import_stock) {

            $import_stock = ImportStock::where('id',  $history_import_stock->import_stock_id)->first();

            $history_import_stock->import_stock_id_ref =   $import_stock->id;
            $history_import_stock->code =   $import_stock->code;
        }

        $revenue_expenditure->history_import_stocks = $history_import_stocks;

        $revenue_expenditure->customer  = null;
        $revenue_expenditure->staff  = null;
        $revenue_expenditure->supplier = null;
        if ($revenue_expenditure->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER) {
            $revenue_expenditure->customer = Customer::where('id',  $revenue_expenditure->recipient_references_id)->first();
        }

        if ($revenue_expenditure->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_STAFF) {
            $revenue_expenditure->staff = Staff::where('id',  $revenue_expenditure->recipient_references_id)->first();
        }

        if ($revenue_expenditure->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER) {
            $revenue_expenditure->supplier = Supplier::where('id',  $revenue_expenditure->recipient_references_id)->first();
        }



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $revenue_expenditure
        ], 200);
    }
}
