<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\SaleCustomer;
use App\Models\SaleCustomerBonusStep;
use App\Models\SaleCustomerConfig;
use App\Models\HistoryBonusSaleCustomer;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\PaySaleCustomer;
use App\Services\BalanceCustomerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * @group  Customer/Sale
 */
class CustomerSaleController extends Controller
{

    /**
     * Sale
     * @bodyParam is_sale bool đăng ký hay không hay không (true false)
     * @bodyParam  payment_auto boolean Bật tự động để user quyết toán 
     * @bodyParam  first_and_last_name Họ và tên
     * @bodyParam  cmnd CMND
     * @bodyParam  date_range ngày cấp
     * @bodyParam  issued_by Nơi cấp
     * @bodyParam  front_card Mặt trước link
     * @bodyParam  back_card Mật sau link
     * @bodyParam  bank Tên ngân hàng
     * @bodyParam  account_number Số tài khoản
     * @bodyParam  account_name Tên tài khoản
     * @bodyParam  branch Chi nhánh
     */
    public function editProfile(Request $request)
    {
        $data = [
            'store_id' => $request->store->id,
            'customer_id' => $request->customer->id,
            'payment_auto' =>  filter_var($request->payment_auto, FILTER_VALIDATE_BOOLEAN),
            'first_and_last_name' => $request->first_and_last_name,
            'cmnd' => $request->cmnd,
            'date_range' => $request->date_range,
            'issued_by' => $request->issued_by,
            'front_card' => $request->front_card,
            'back_card' => $request->back_card,
            'bank' => $request->bank,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'branch' => $request->branch,
        ];


        if ($request->customer->official == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => "Khách hàng chưa chính thức hãy đăng ký lại",
            ], 400);
        }

        $sale = SaleCustomer::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first();

        if ($sale == null) {
            SaleCustomer::create(
                $data
            );
        } else {
            $sale->update(
                $data
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => SaleCustomer::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first()
        ], 200);
    }


    public function info_account(Request $request)
    {

        $sale = SaleCustomer::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($sale == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NOT_REGISTERED_COLLABORATOR[0],
                'msg' => MsgCode::NOT_REGISTERED_COLLABORATOR[1],
            ], 400);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => SaleCustomer::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first()
        ], 200);
    }

    /**
     * Thông tin tổng quan
     * 
     * Doanh thu hiện tại
     * 
     * type_rose 0 doanh sô - 1 hoa hồng
     * 
     * total_final daonh số tháng này
     * 
     * share_sale tổng tiền hoa đồng chia sẻ
     * 
     * received_month_bonus Đã nhận thưởng tháng hay chưa
     * 
     * number_order số lượng đơn hàng tháng này
     * 
     * allow_payment_request cho phép yêu cầu thanh toán
     * 
     * payment_1_of_month định kỳ thanh toán 1
     * 
     * payment_16_of_month định kỳ thanh toán 15
     * 
     * payment_limit Giới hạn yêu cầu thanh toán 
     * 
     * has_payment_request có yêu cầu thanh toán hay không
     * 
     * money_payment_request Số tiền yêu cầu hiện tại
     * 
     */
    public function info_overview(Request $request)
    {

        $sale = SaleCustomer::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($sale == null) {
            $sale = SaleCustomer::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $request->customer->id
                ]
            );
        }

        $configExists = SaleCustomerConfig::where(
            'store_id',
            $request->store->id
        )->first();


        if ($configExists == null) {
            return response()->json([
                'code' => 300,
                'success' => false,
                'msg_code' => MsgCode::STORE_HAS_NOT_CONFIGURED[0],
                'msg' => MsgCode::STORE_HAS_NOT_CONFIGURED[1],
            ], 300);
        }


        $payAfter = PaySaleCustomer::where('store_id', $request->store->id)
            ->where('sale_id',  $sale->id)->where('status', 0)->first();

        $steps = SaleCustomerBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();;

        $now = Helper::getTimeNowDateTime();

        $orders = Order::where('store_id', $request->store->id)
            ->where('sale_by_customer_id', $request->customer->id)
            ->where('created_at', '>=',  $now->format('Y-m-01 H:i:s'))
            ->where('created_at', '<', $now->format('Y-m-d H:i:s'))
            ->get();

        $share_sale = 0;
        $total_final = 0;
        $count = 0;
        foreach ($orders as  $order) {

            if ($order->order_status == StatusDefineCode::COMPLETED || $order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {

                $count = $count + 1;
                $share_sale += $order->share_sale;

                if ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                    $total_final = $total_final - $order->total_final;
                } else {
                    $total_final = $total_final + $order->total_final;
                }
            }
        }

        $history = HistoryBonusSaleCustomer::where('store_id', $request->store->id)
            ->where('sale_id', $sale->id)
            ->where('year', $request->year)
            ->where('month', $request->month)->first();


        $data = [
            "balance" =>  $sale->balance,
            "share_sale" =>  $share_sale,
            "total_final" =>  $total_final,
            "number_order" => $count,
            "received_month_bonus" =>  $history == null ? false : true,
            "type_rose" => $configExists->type_rose ?? 0,
            "allow_payment_request" => $configExists->allow_payment_request,
            "payment_1_of_month" => $configExists->payment_1_of_month,
            "payment_16_of_month" => $configExists->payment_16_of_month,
            "payment_limit" =>  $configExists->payment_limit,
            "has_payment_request" =>   $payAfter  == null ? false : true,
            "money_payment_request" =>  $payAfter  == null ? null :  $payAfter->money,
            "steps_bonus" => $steps,
        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ], 200);
    }

    /**
     * Đăng ký ctv
     * @bodyParam is_sale bool đăng ký hay không hay không (true false)
     */
    public function regSaleCustomer(Request $request)
    {

        $is_sale = filter_var($request->is_sale, FILTER_VALIDATE_BOOLEAN);
        $request->customer->update([
            'is_sale' =>  $is_sale,
            'official' => true,
        ]);

        if (filter_var($request->is_sale, FILTER_VALIDATE_BOOLEAN) == true) {
            $sale = SaleCustomer::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first();

            if ($sale == null) {
                SaleCustomer::create(
                    [
                        'store_id' => $request->store->id,
                        'customer_id' =>  $request->customer->id
                    ]
                );
            }
        }

        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Yêu cầu làm cộng tác viên mới',
            'Khách hàng ' . $request->customer->name . ' yêu cầu làm CTV ',
            TypeFCM::GET_CTV,
            $request->customer->id,
            null
        );



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function data_total_order(Request $request)
    {

        $sale = SaleCustomer::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($sale == null) {
            return [];
        }

        $res = Order::where('store_id', $request->store->id)
            ->where('sale_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->selectRaw('year(created_at) year, month(created_at) month, sum(total_final) total_final, sum(total_final) share_sale')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->get();

        $data = [];

        foreach ($res  as $itemOrder) {
            $history = HistoryBonusSaleCustomer::where('store_id', $request->store->id)
                ->where('sale_id', $sale->id)
                ->where('year', $itemOrder->year)
                ->where('month', $itemOrder->month)->first();

            //////////////

            $money_current = 0;
            $money_bonus_current = 0;

            $steps = SaleCustomerBonusStep::where('store_id', $request->store->id)->orderBy('limit', 'desc')->get();
            $configExists = SaleCustomerConfig::where(
                'store_id',
                $request->store->id
            )->first();

            //Theo doanh số
            if ($configExists->type_rose == 0) {
                $money_current = $itemOrder->total_final ?? 0;
            }

            //Doanh hoa hồng
            if ($configExists->type_rose == 1) {
                $money_current = $itemOrder->share_sale ?? 0;
            }


            foreach ($steps as $step) {
                if ($step->limit <= $money_current) {
                    $use_limit_config = $step->limit;
                    $money_bonus_current = $step->bonus;
                    break;
                }
            }

            //////////////  //////////////  //////////////

            array_push($data, [
                'year' => $itemOrder->year,
                'month' => $itemOrder->month,
                'total_final' => $itemOrder->total_final ?? 0,
                'share_sale' => $itemOrder->share_sale ?? 0,
                'awarded' => $history == null ? false : true,
                'money_bonus_rewarded' =>  $history->money_bonus_rewarded ?? 0,
                'money_bonus_current' =>  $money_bonus_current  ?? 0
            ]);
        }

        return     $data;
    }

    /**
     * Báo cáo thưởng  bậc thang
     */
    public function list_bonus_with_month(Request $request)
    {

        $data = $this->data_total_order($request);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>     $this->paginate($data)
        ], 200);
    }

    /**
     * Nhận thưởng tháng
     * @bodyParam month Tháng muốn nhận VD: 2
     * @bodyParam year Năm muốn nhận VD: 2012
     */
    public function take_bonus(Request $request)
    {

        $sale = SaleCustomer::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($sale == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NOT_REGISTERED_COLLABORATOR[0],
                'msg' => MsgCode::NOT_REGISTERED_COLLABORATOR[1],
            ], 400);
        }

        if ($request->month < 1 || $request->month > 12) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_MONTH[0],
                'msg' => MsgCode::INVALID_MONTH[1],
            ], 400);
        }

        if ($request->year == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_YEAR[0],
                'msg' => MsgCode::INVALID_YEAR[1],
            ], 400);
        }

        $data = $this->data_total_order($request);


        $has_time = false;
        $data_money_sale = null;
        foreach ($data as $item) {
            if ($item["year"] == $request->year  && $item["month"] == $request->month) {
                $has_time = true;
                $data_money_sale  = $item;
                break;
            }
        }

        if ($has_time  == false) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDERS_IN_TIME[0],
                'msg' => MsgCode::NO_ORDERS_IN_TIME[1],
            ], 400);
        }

        $steps = SaleCustomerBonusStep::where('store_id', $request->store->id)->orderBy('limit', 'desc')->get();

        $configExists = SaleCustomerConfig::where(
            'store_id',
            $request->store->id
        )->first();

        if (count($steps) == 0 ||    $configExists  == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_BONUS_INSTALLED[0],
                'msg' => MsgCode::NO_BONUS_INSTALLED[1],
            ], 400);
        }

        $use_limit_config = null;
        $money_current = null;
        $money_bonus_rewarded = 0;


        //Theo doanh số
        if ($configExists->type_rose == 0) {
            $money_current = $data_money_sale["total_final"];
        }

        //Doanh hoa hồng
        if ($configExists->type_rose == 1) {
            $money_current = $data_money_sale["share_sale"];
        }


        foreach ($steps as $step) {
            if ($step->limit <= $money_current) {
                $use_limit_config = $step->limit;
                $money_bonus_rewarded = $step->bonus;
                break;
            }
        }

        if ($use_limit_config == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NOT_ELIGIBLE_REWARD[0],
                'msg' => MsgCode::NOT_ELIGIBLE_REWARD[1],
            ], 400);
        }

        $history = HistoryBonusSaleCustomer::where('store_id', $request->store->id)
            ->where('sale_id', $sale->id)
            ->where('year', $request->year)
            ->where('month', $request->month)->first();

        if ($history != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::RECEIVED_MONTH_BONUS[0],
                'msg' => MsgCode::RECEIVED_MONTH_BONUS[1],
            ], 400);
        }

        $history = HistoryBonusSaleCustomer::create([
            "store_id" => $request->store->id,
            "sale_id" => $sale->id,
            "year" => $request->year,
            "month" => $request->month,
            "money_bonus_rewarded" => $money_bonus_rewarded,
            "limit" => $use_limit_config,
        ]);

        BalanceCustomerService::change_balance_collaborator(
            $request->store->id,
            $request->customer->id,
            BalanceCustomerService::BONUS_MONTH,
            $money_bonus_rewarded,
            $history->id,
            $request->month . "/" . $request->year
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
