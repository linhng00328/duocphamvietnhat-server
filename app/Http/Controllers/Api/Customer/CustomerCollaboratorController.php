<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\Collaborator;
use App\Models\CollaboratorBonusStep;
use App\Models\CollaboratorRegisterRequest;
use App\Models\CollaboratorsConfig;
use App\Models\Customer;
use App\Models\HistoryBonusCollaborator;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\PayCollaborator;
use App\Services\BalanceCustomerService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * @group  Customer/CTV
 */
class CustomerCollaboratorController extends Controller
{

    /**
     * Thông tin cộng tác viên
     * @bodyParam is_collaborator bool đăng ký hay không hay không (true false)
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

        $collaborator = Collaborator::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first();

        if ($collaborator == null) {
            Collaborator::create(
                $data
            );
        } else {
            $collaborator->update(
                $data
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Collaborator::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first()
        ], 200);
    }


    public function info_account(Request $request)
    {

        $collaborator = Collaborator::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($collaborator == null) {
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
            'data' => Collaborator::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first()
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
     * share_collaborator tổng tiền hoa đồng chia sẻ
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

        $collaborator = Collaborator::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($collaborator == null) {
            $collaborator = Collaborator::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $request->customer->id
                ]
            );
        }

        $configExists = CollaboratorsConfig::where(
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


        $payAfter = PayCollaborator::where('store_id', $request->store->id)
            ->where('collaborator_id',  $collaborator->id)->where('status', 0)->first();

        $steps = CollaboratorBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();;

        $now = Helper::getTimeNowDateTime();

        $order_comissions = Order::where('store_id', $request->store->id)
            ->where(function ($query) use ($request) {

                $query->where('collaborator_by_customer_id', $request->customer->id)
                    ->orWhere('collaborator_by_customer_referral_id', $request->customer->id);
            })
            ->where('created_at', '>=',  $now->format('Y-m-01 00:00:00'))
            ->where('created_at', '<=', $now->format('Y-m-d 23:59:59'))
            ->get();

        $total_share_collaborator = 0;
        $count_commission = 0;

        foreach ($order_comissions as  $order) {

            if ($order->order_status == StatusDefineCode::COMPLETED || $order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {

                if ($order->order_status == StatusDefineCode::COMPLETED) {
                    $count_commission += 1;
                }

                if ($order->collaborator_by_customer_referral_id == $request->customer->id && $order->share_collaborator_referen > 0) {
                    $total_share_collaborator +=  ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS ? -$order->share_collaborator_referen : $order->share_collaborator_referen);
                } else if ($order->collaborator_by_customer_id == $request->customer->id && $order->share_collaborator > 0) {
                    $total_share_collaborator += ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS ? -$order->share_collaborator : $order->share_collaborator);
                }
            }
        }

        $total_final = 0;
        $count = 0;

        $order_import = Order::where('store_id', $request->store->id)
            ->where(function ($query) use ($request) {

                $query->where('customer_id', $request->customer->id)
                    ->orWhere('collaborator_by_customer_id', $request->customer->id);
            })
            ->where('created_at', '>=',  $now->format('Y-m-01 00:00:00'))
            ->where('created_at', '<=', $now->format('Y-m-d 23:59:59'))
            ->get();

        foreach ($order_import as  $order) {

            if ($order->order_status == StatusDefineCode::COMPLETED || $order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {

                $count = $count + 1;

                if ($order->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {

                    $total_final = $total_final - $order->total_final;
                } else {

                    $total_final = $total_final + $order->total_final;
                }
            }
        }

        $history = HistoryBonusCollaborator::where('store_id', $request->store->id)
            ->where('collaborator_id', $collaborator->id)
            ->where('year', $request->year)
            ->where('month', $request->month)->first();

        $data = [
            "balance" =>  $collaborator->balance,
            "share_collaborator" =>  $total_share_collaborator,
            "number_order_comission" => $count_commission,
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
     * @bodyParam is_collaborator bool đăng ký hay không hay không (true false)
     * 
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
     * 
     */
    public function regCollaborator(Request $request)
    {

        // if ($request->is_collaborator == true) {
        //     $is_collaborator = filter_var($request->is_collaborator, FILTER_VALIDATE_BOOLEAN);
        //     $request->customer->update([
        //         'is_collaborator' =>  $is_collaborator,
        //         'official' => true,
        //     ]);

        //     if (filter_var($request->is_collaborator, FILTER_VALIDATE_BOOLEAN) == true) {
        //         $collaborator = Collaborator::where('store_id', $request->store->id)
        //             ->where('customer_id', $request->customer->id)->first();

        //         if ($collaborator == null) {
        //             Collaborator::create(
        //                 [
        //                     'store_id' => $request->store->id,
        //                     'customer_id' =>  $request->customer->id,
        //                     'status' => 1
        //                 ]
        //             );
        //         } else {
        //             $collaborator->update([
        //                 'store_id' => $request->store->id,
        //                 'customer_id' =>  $request->customer->id,
        //                 'status' => 1
        //             ]);
        //         }
        //     }

        //     PushNotificationUserJob::dispatch(
        //         $request->store->id,
        //         $request->store->user_id,
        //         'Yêu cầu làm cộng tác viên mới',
        //         'Khách hàng ' . $request->customer->name . ' yêu cầu làm CTV ',
        //         TypeFCM::GET_CTV,
        //         $request->customer->id,
        //         null
        //     );



        //     return response()->json([
        //         'code' => 200,
        //         'success' => true,
        //         'msg_code' => MsgCode::SUCCESS[0],
        //         'msg' => MsgCode::SUCCESS[1],
        //     ], 200);
        // } else {
        $collaboratorRegisterRequest = CollaboratorRegisterRequest::where('store_id', $request->store->id)
            ->orderBy('id', 'desc')
            ->where('customer_id', $request->customer->id)->first();

        if ($collaboratorRegisterRequest != null && ($collaboratorRegisterRequest->status == 0 ||
            $collaboratorRegisterRequest->status == 3
        )) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Bạn đã yêu cầu làm cộng tác viên và đang đợi xử lý',
            ], 400);
        }

        $request->customer->update([
            'is_collaborator' =>  false,
            'official' => true,
        ]);

        $collaborator = Collaborator::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        $data =  [
            'store_id' => $request->store->id,
            'customer_id' =>  $request->customer->id,
            'first_and_last_name' => $request->first_and_last_name ??  $collaborator->first_and_last_name,
            'cmnd' => $request->cmnd ??  $collaborator->cmnd,
            'date_range' => $request->date_range ??  $collaborator->date_range,
            'issued_by' => $request->issued_by ??  $collaborator->issued_by,
            'front_card' => $request->front_card ??  $collaborator->front_card,
            'back_card' => $request->back_card ??  $collaborator->back_card,
            'bank' => $request->bank ??  $collaborator->bank,
            'account_number' => $request->account_number ??  $collaborator->account_number,
            'account_name' => $request->account_name ??  $collaborator->account_name,
            'branch' => $request->branch ??  $collaborator->branch,
        ];
        if ($collaborator == null) {
            $collaborator =   Collaborator::create(
                $data
            );
        } else {
            $collaborator->update($data);
        }

        $status = 0;

        $collaboratorRegisterRequestLast = CollaboratorRegisterRequest::where('store_id', $request->store->id)
            ->orderBy('id', 'desc')
            ->where('customer_id', $request->customer->id)->first();

        if ($collaboratorRegisterRequestLast != null && $collaboratorRegisterRequestLast->status == 1) {
            $status = 3;
        }

        CollaboratorRegisterRequest::create([
            'status' =>   $status,
            'store_id' => $request->store->id,
            'customer_id' =>  $request->customer->id,
            'collaborator_id' =>   $collaborator->id,
        ]);

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
        // }
    }

    public function data_total_order(Request $request)
    {

        $collaborator = Collaborator::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($collaborator == null) {
            return [];
        }

        $res = Order::where('store_id', $request->store->id)
            ->where('collaborator_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->selectRaw('year(created_at) year, month(created_at) month, sum(total_final) total_final, sum(share_collaborator) share_collaborator')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->get();

        $data = [];

        foreach ($res  as $itemOrder) {
            $history = HistoryBonusCollaborator::where('store_id', $request->store->id)
                ->where('collaborator_id', $collaborator->id)
                ->where('year', $itemOrder->year)
                ->where('month', $itemOrder->month)->first();

            //////////////

            $money_current = 0;
            $money_bonus_current = 0;

            $steps = CollaboratorBonusStep::where('store_id', $request->store->id)->orderBy('limit', 'desc')->get();
            $configExists = CollaboratorsConfig::where(
                'store_id',
                $request->store->id
            )->first();

            //Theo doanh số
            if ($configExists->type_rose == 0) {
                $money_current = $itemOrder->total_final ?? 0;
            }

            //Doanh hoa hồng
            if ($configExists->type_rose == 1) {
                $money_current = $itemOrder->share_collaborator ?? 0;
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
                'share_collaborator' => $itemOrder->share_collaborator ?? 0,
                'awarded' => $history == null ? false : true,
                'money_bonus_rewarded' =>  $history->money_bonus_rewarded ?? 0,
                'money_bonus_current' =>  $money_bonus_current  ?? 0
            ]);
        }

        return     $data;
    }

    /**
     * Lấy danh sách GT
     * 
     * @urlParam  store_code required Store code
     * 
     * @bodyParam referral_phone_number 
     * 
     */
    public function getAllReferralPhoneNumberCTV(Request $request)
    {
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');

        $dateFromDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 00:00:00';
        $dateToDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $cus = Customer::where('store_id', $request->store->id)
            ->where('referral_phone_number', $request->customer->phone_number)
            ->search(request('search'))
            ->paginate(20);

        foreach ($cus as $p) {
            $total_after_discount_no_bonus =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where(function ($query) use ($request) {
                    $query->where('collaborator_by_customer_referral_id', $request->customer->id)
                        ->orWhere("collaborator_by_customer_id", "=", $request->customer->id);
                })
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));
            $count_orders =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where(function ($query) use ($request) {
                    $query->where('collaborator_by_customer_referral_id', $request->customer->id)
                        ->orWhere("collaborator_by_customer_id", "=", $request->customer->id);
                })
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->count();

            $total_share_collaborator =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where('collaborator_by_customer_id', $request->customer->id)
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->sum('share_collaborator');

            $total_share_collaborator_referen =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where('collaborator_by_customer_referral_id', $request->customer->id)
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->sum('share_collaborator_referen');

            $p->total_final = $total_after_discount_no_bonus;
            $p->count_orders = $count_orders;
            $p->total_share_collaborator = $total_share_collaborator;
            $p->total_share_collaborator_referen = $total_share_collaborator_referen;
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $cus,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
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

        $collaborator = Collaborator::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($collaborator == null) {
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
        $data_money_collaborator = null;
        foreach ($data as $item) {
            if ($item["year"] == $request->year  && $item["month"] == $request->month) {
                $has_time = true;
                $data_money_collaborator  = $item;
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

        $steps = CollaboratorBonusStep::where('store_id', $request->store->id)->orderBy('limit', 'desc')->get();

        $configExists = CollaboratorsConfig::where(
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
            $money_current = $data_money_collaborator["total_final"];
        }

        //Doanh hoa hồng
        if ($configExists->type_rose == 1) {
            $money_current = $data_money_collaborator["share_collaborator"];
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

        $history = HistoryBonusCollaborator::where('store_id', $request->store->id)
            ->where('collaborator_id', $collaborator->id)
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

        $history = HistoryBonusCollaborator::create([
            "store_id" => $request->store->id,
            "collaborator_id" => $collaborator->id,
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
