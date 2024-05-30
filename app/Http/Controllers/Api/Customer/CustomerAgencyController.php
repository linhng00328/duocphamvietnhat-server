<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\Agency;
use App\Models\AgencyBonusStep;
use App\Models\AgencyImportStep;

use App\Models\AgencyConfig;
use App\Models\AgencyRegisterRequest;
use App\Models\AgencysConfig;
use App\Models\Customer;
use App\Models\HistoryBonusAgency;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\PayAgency;
use App\Services\BalanceCustomerService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * @group  Customer/Đại lý
 */
class CustomerAgencyController extends Controller
{

    /**
     * Thông tin Đại lý
     * @bodyParam is_agency bool đăng ký hay không hay không (true false)
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

        $agency = Agency::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first();

        if ($agency == null) {
            Agency::create(
                $data
            );
        } else {
            $agency->update(
                $data
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Agency::where('store_id', $request->store->id)
                ->where('customer_id', $request->customer->id)->first()
        ], 200);
    }


    public function info_account(Request $request)
    {

        $agency = Agency::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($agency == null) {
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
            'data' => Agency::where('store_id', $request->store->id)->where('customer_id', $request->customer->id)->first()
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
     * share_agency tổng tiền hoa đồng chia sẻ
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

        $carbon = Carbon::now('Asia/Ho_Chi_Minh');

        $dateFromDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 00:00:00';
        $dateToDay = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $dateFromMonth = $carbon->year . '-' . $carbon->month . '-' . "01" . ' 00:00:00';
        $dateToMonth = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $dateFromYear = $carbon->year . '-' . "01" . '-' . "01" . ' 00:00:00';
        $dateToYear = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $date = new \Carbon\Carbon();
        $firstOfQuarter = $date->firstOfQuarter();
        $lastOfQuarter = $date->lastOfQuarter();

        $curMonth = date("m", time());
        $curQuarter = (int)ceil($curMonth / 3);

        $dateFromQuarter = $firstOfQuarter->year . '-' .  $curQuarter . '-' . "01" . ' 00:00:00';
        $dateToQuarter =  $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' 23:59:59';

        $day = date('w');
        $week_start = date('Y-m-d', strtotime('-' . $day . ' days'));
        $week_end = date('Y-m-d', strtotime('+' . (6 - $day) . ' days'));

        $dateFromWeek = $week_start  . ' 00:00:00';
        $dateToWeek = $week_end . ' 23:59:59';


        // $total_order = Order::where('store_id', $request->store->id)
        //     ->where('agency_by_customer_id', $request->customer->id)
        //     ->where('order_status', StatusDefineCode::COMPLETED)
        //     ->where('payment_status', StatusDefineCode::PAID)
        //     ->sum('total_final');

        //  whereIn('orders.phone_number', [0977711111,0955566666,0987654321,0988776611,0977744444])
        //
        $count_in_day = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromDay)
            ->where('orders.created_at', '<=', $dateToDay)
            ->count();

        $total_final_in_day = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromDay)
            ->where('orders.created_at', '<=', $dateToDay)
            ->sum('total_final');
        //

        $count_in_month = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromMonth)
            ->where('orders.created_at', '<=', $dateToMonth)
            ->count();

        $total_final_in_month = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromMonth)
            ->where('orders.created_at', '<=', $dateToMonth)
            ->sum('total_final');
        //
        $count_in_week = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromWeek)
            ->where('orders.created_at', '<=', $dateToWeek)
            ->count();

        $total_final_in_week = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromWeek)
            ->where('orders.created_at', '<=', $dateToWeek)
            ->sum('total_final');
        //
        $count_in_year = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromYear)
            ->where('orders.created_at', '<=', $dateToYear)
            ->count();

        $total_final_in_year = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromYear)
            ->where('orders.created_at', '<=', $dateToYear)
            ->sum('total_final');
        //

        $count_in_quarter = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromQuarter)
            ->where('orders.created_at', '<=', $dateToQuarter)
            ->count();

        $total_final_in_quarter = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromQuarter)
            ->where('orders.created_at', '<=', $dateToQuarter)
            ->sum('total_final');

        //-//////////////

        $total_after_discount_no_bonus_in_day = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromDay)
            ->where('orders.created_at', '<=', $dateToDay)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));
        //

        $total_after_discount_no_bonus_in_month = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromMonth)
            ->where('orders.created_at', '<=', $dateToMonth)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $total_after_discount_no_bonus_in_week = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromWeek)
            ->where('orders.created_at', '<=', $dateToWeek)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $total_after_discount_no_bonus_in_year = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromYear)
            ->where('orders.created_at', '<=', $dateToYear)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));

        $total_after_discount_no_bonus_in_quarter = Order::where('store_id', $request->store->id)
            ->where('agency_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('orders.created_at', '>=', $dateFromQuarter)
            ->where('orders.created_at', '<=', $dateToQuarter)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $agency = Agency::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($agency == null) {
            $agency = Agency::create(
                [
                    'store_id' => $request->store->id,
                    'customer_id' =>  $request->customer->id
                ]
            );
        }

        $configExists = AgencyConfig::where(
            'store_id',
            $request->store->id
        )->first();


        if ($configExists == null) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => [
                    'count_in_day' =>  $count_in_day,
                    'total_final_in_day' =>  $total_final_in_day,

                    'count_in_month' =>  $count_in_month,
                    'total_final_in_month' =>  $total_final_in_month,

                    'count_in_week' =>  $count_in_week,
                    'total_final_in_week' =>  $total_final_in_week,

                    'count_in_year' =>  $count_in_year,
                    'total_final_in_year' =>  $total_final_in_year,

                    'count_in_quarter' =>  $count_in_quarter,
                    'total_final_in_quarter' =>  $total_final_in_quarter,

                    //

                    'total_after_discount_no_bonus_in_day' =>  $total_after_discount_no_bonus_in_day,


                    'total_after_discount_no_bonus_in_month' =>  $total_after_discount_no_bonus_in_month,


                    'total_after_discount_no_bonus_in_week' =>  $total_after_discount_no_bonus_in_week,


                    'total_after_discount_no_bonus_in_year' =>  $total_after_discount_no_bonus_in_year,


                    'total_after_discount_no_bonus_in_quarter' =>  $total_after_discount_no_bonus_in_quarter,

                    "balance" =>  0,
                    "total_final" => 0,
                    "number_order" => 0,
                    "share_agency_ctv" =>  0,
                    "total_final_ctv" =>  0,
                    "number_order_ctv" => 0,
                    "received_month_bonus" => 0,
                    "type_rose" => 0,
                    "allow_payment_request" => false,
                    "payment_1_of_month" => 0,
                    "payment_16_of_month" => 0,
                    "payment_limit" =>  0,
                    "has_payment_request" => false,
                    "money_payment_request" => 0,
                    "steps_bonus" => array(),
                ]
            ], 200);
        }


        $payAfter = PayAgency::where('store_id', $request->store->id)
            ->where('agency_id',  $agency->id)->where('status', 0)->first();

        //Thưởng hoa hồng
        $steps = AgencyBonusStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();
        // Thưởng doanh số
        $steps_import = AgencyImportStep::where('store_id', $request->store->id)->orderBy('bonus', 'asc')->get();


        $now = Helper::getTimeNowDateTime();


        //Phần nhập hàng
        $total_final = Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('agency_by_customer_id', $request->customer->id)
            ->sum('total_final');

        $total_after_discount_no_bonus = Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('agency_by_customer_id', $request->customer->id)
            ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));


        $count = Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('agency_by_customer_id', $request->customer->id)->count();

        $history = HistoryBonusAgency::where('store_id', $request->store->id)
            ->where('agency_id', $agency->id)
            ->where('year', $request->year)
            ->where('month', $request->month)->first();

        $total_final_ctv = Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('agency_ctv_by_customer_id', $request->customer->id)
            ->orWhere('agency_ctv_by_customer_referral_id', $request->customer->id)
            ->whereBetween('created_at', [$now->format('Y-m-01 00:00:00'), $now->format('Y-m-d H:i:s')])
            ->sum('total_final');

        $share_agency_ctv = Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('agency_ctv_by_customer_id', $request->customer->id)
            ->whereBetween('created_at', [$now->format('Y-m-01 00:00:00'), $now->format('Y-m-d H:i:s')])
            ->sum('share_agency');

        $share_agency_ctv =  $share_agency_ctv + Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('agency_ctv_by_customer_referral_id', $request->customer->id)
            ->whereBetween('created_at', [$now->format('Y-m-01 00:00:00'), $now->format('Y-m-d H:i:s')])
            ->sum('share_agency_referen');

        $count_ctv = Order::where('store_id', $request->store->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where(function ($query) use ($request) {
                $query->where('agency_ctv_by_customer_id', $request->customer->id)
                    ->orWhere('agency_ctv_by_customer_referral_id', $request->customer->id);
            })
            ->where(function ($query) use ($request) {
                $query->where('orders.share_agency', '>', 0)
                    ->orWhere('orders.share_agency_referen', '>', 0);
            })
            ->whereBetween('created_at', [$now->format('Y-m-01 00:00:00'), $now->format('Y-m-d H:i:s')])
            ->count();

        $data = [
            'count_in_day' =>  $count_in_day,
            'total_final_in_day' =>  $total_final_in_day,

            'count_in_month' =>  $count_in_month,
            'total_final_in_month' =>  $total_final_in_month,

            'count_in_week' =>  $count_in_week,
            'total_final_in_week' =>  $total_final_in_week,

            'count_in_year' =>  $count_in_year,
            'total_final_in_year' =>  $total_final_in_year,

            'count_in_quarter' =>  $count_in_quarter,
            'total_final_in_quarter' =>  $total_final_in_quarter,

            'total_after_discount_no_bonus_in_day' =>  $total_after_discount_no_bonus_in_day,


            'total_after_discount_no_bonus_in_month' =>  $total_after_discount_no_bonus_in_month,


            'total_after_discount_no_bonus_in_week' =>  $total_after_discount_no_bonus_in_week,


            'total_after_discount_no_bonus_in_year' =>  $total_after_discount_no_bonus_in_year,


            'total_after_discount_no_bonus_in_quarter' =>  $total_after_discount_no_bonus_in_quarter,

            "balance" =>  $agency->balance,
            "total_final" =>  $total_final,
            "total_after_discount_no_bonus" =>  $total_after_discount_no_bonus,
            "number_order" => $count,
            "share_agency_ctv" =>  $share_agency_ctv,
            "total_final_ctv" =>  $total_final_ctv,
            "number_order_ctv" => $count_ctv,
            "received_month_bonus" =>  $history == null ? false : true,
            "type_rose" => $configExists->type_rose ?? 0,
            "allow_payment_request" => $configExists->allow_payment_request,
            "type_bonus_period_import" => $configExists->type_bonus_period_import,

            "payment_1_of_month" => $configExists->payment_1_of_month,
            "payment_16_of_month" => $configExists->payment_16_of_month,
            "payment_limit" =>  $configExists->payment_limit,
            "has_payment_request" =>   $payAfter  == null ? false : true,
            "money_payment_request" =>  $payAfter  == null ? null :  $payAfter->money,
            "steps_bonus" => $steps,
            "steps_import" => $steps_import,
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
     * Đăng ký dai ly
     * @bodyParam is_agency bool đăng ký hay không hay không (true false)
     */
    public function regAgency(Request $request)
    {

        // $is_agency = filter_var($request->is_agency, FILTER_VALIDATE_BOOLEAN);
        // $request->customer->update([
        //     'is_agency' =>  $is_agency,
        //     'official' => true,
        // ]);

        // if (filter_var($request->is_agency, FILTER_VALIDATE_BOOLEAN) == true) {
        //     $agency = Agency::where('store_id', $request->store->id)
        //         ->where('customer_id', $request->customer->id)->first();

        //     if ($agency == null) {
        //         Agency::create(
        //             [
        //                 'store_id' => $request->store->id,
        //                 'customer_id' =>  $request->customer->id
        //             ]
        //         );
        //     }
        // }

        // PushNotificationUserJob::dispatch(
        //     $request->store->id,
        //     $request->store->user_id,
        //     'Yêu cầu làm đại lý mới',
        //     'Khách hàng ' . $request->customer->name . ' yêu cầu làm đại lý ',
        //     TypeFCM::GET_AGENCY,
        //     $request->customer->id,
        //     null
        // );

        // return response()->json([
        //     'code' => 200,
        //     'success' => true,
        //     'msg_code' => MsgCode::SUCCESS[0],
        //     'msg' => MsgCode::SUCCESS[1],
        // ], 200);
        $agencyRegisterRequest = AgencyRegisterRequest::where('store_id', $request->store->id)
            ->orderBy('id', 'desc')
            ->where('customer_id', $request->customer->id)->first();

        if ($agencyRegisterRequest != null && ($agencyRegisterRequest->status == 0 ||
            $agencyRegisterRequest->status == 3
        )) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => 'Bạn đã yêu cầu làm đại lý và đang đợi xử lý',
            ], 400);
        }

        $request->customer->update([
            'is_agency' =>  false,
            'official' => true,
        ]);

        $agency = Agency::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        $data =  [
            'store_id' => $request->store->id,
            'customer_id' =>  $request->customer->id,
            'first_and_last_name' => $request->first_and_last_name ??  ($agency != null ? $agency->first_and_last_name : null),
            'cmnd' => $request->cmnd ??  $agency->cmnd,
            'date_range' => $request->date_range ??  $agency->date_range,
            'issued_by' => $request->issued_by ??  $agency->issued_by,
            'front_card' => $request->front_card ??  $agency->front_card,
            'back_card' => $request->back_card ??  $agency->back_card,
            'bank' => $request->bank ??  $agency->bank,
            'account_number' => $request->account_number ??  $agency->account_number,
            'account_name' => $request->account_name ??  $agency->account_name,
            'branch' => $request->branch ??  $agency->branch,
        ];
        if ($agency == null) {
            $agency = Agency::create(
                $data
            );
        } else {
            $agency->update($data);
        }

        $status = 0;

        if ($agencyRegisterRequest != null && $agencyRegisterRequest->status == 1) {
            $status = 3;
        }

        AgencyRegisterRequest::create([
            'status' =>   $status,
            'store_id' => $request->store->id,
            'customer_id' =>  $request->customer->id,
            'agency_id' =>   $agency->id,
        ]);

        PushNotificationUserJob::dispatch(
            $request->store->id,
            $request->store->user_id,
            'Yêu cầu làm đại lý mới',
            'Khách hàng ' . $request->customer->name . ' yêu cầu làm đại lý ',
            TypeFCM::GET_AGENCY,
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

    /**
     * Lấy danh sách GT Agency
     * 
     * @urlParam  store_code required Store code
     * 
     * @bodyParam referral_phone_number 
     * 
     */
    public function getAllReferralPhoneNumberAgency(Request $request)
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
                    $query->where("agency_ctv_by_customer_referral_id", "=", $request->customer->id)
                        ->orWhere("agency_ctv_by_customer_id", "=", $request->customer->id);
                })
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));
            $count_orders =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where(function ($query) use ($request) {
                    $query->where("agency_ctv_by_customer_referral_id", "=", $request->customer->id)
                        ->orWhere("agency_ctv_by_customer_id", "=", $request->customer->id);
                })
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', ($request->date_to ?? $dateToDay))
                ->count();
            $total_share_agency =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where("agency_ctv_by_customer_id", "=", $request->customer->id)
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->sum('share_agency');

            $total_share_agency_referen =   DB::table('orders')->where('store_id', $request->store->id)
                ->where('customer_id', $p->id)
                ->where("agency_ctv_by_customer_referral_id", "=", $request->customer->id)
                ->where('orders.order_status', StatusDefineCode::COMPLETED)
                ->where('orders.payment_status', StatusDefineCode::PAID)
                ->whereDate('created_at', '>=', $request->date_from ?? $dateFromDay)
                ->whereDate('created_at', '<=', $request->date_to ?? $dateToDay)
                ->sum('share_agency_referen');

            $p->total_final = $total_after_discount_no_bonus;
            $p->count_orders = $count_orders;
            $p->total_share_agency = $total_share_agency;
            $p->total_share_agency_referen = $total_share_agency_referen;
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $cus,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function data_total_order(Request $request)
    {

        $agency = Agency::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($agency == null) {
            return [];
        }

        $res = Order::where('store_id', $request->store->id)
            ->where('agency_ctv_by_customer_id', $request->customer->id)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->where('payment_status', StatusDefineCode::PAID)
            ->selectRaw('year(created_at) year, month(created_at) month, sum(total_final) total_final, sum(share_agency) share_agency')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->get();

        $data = [];

        foreach ($res  as $itemOrder) {
            $history = HistoryBonusAgency::where('store_id', $request->store->id)
                ->where('agency_id', $agency->id)
                ->where('year', $itemOrder->year)
                ->where('month', $itemOrder->month)->first();

            //////////////

            $money_current = 0;
            $money_bonus_current = 0;

            $steps = AgencyBonusStep::where('store_id', $request->store->id)->orderBy('limit', 'desc')->get();
            $configExists = AgencyConfig::where(
                'store_id',
                $request->store->id
            )->first();

            //Theo doanh số
            if ($configExists->type_rose == 0) {
                $money_current = $itemOrder->total_final ?? 0;
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
                'share_agency' => $itemOrder->share_agency ?? 0,
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

        $agency = Agency::where('store_id', $request->store->id)
            ->where('customer_id', $request->customer->id)->first();

        if ($agency == null) {
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
        $data_money_agency = null;
        foreach ($data as $item) {
            if ($item["year"] == $request->year  && $item["month"] == $request->month) {
                $has_time = true;
                $data_money_agency  = $item;
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

        $steps = AgencyBonusStep::where('store_id', $request->store->id)->orderBy('limit', 'desc')->get();

        $configExists = AgencyConfig::where(
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
            $money_current = $data_money_agency["total_final"];
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

        $history = HistoryBonusAgency::where('store_id', $request->store->id)
            ->where('agency_id', $agency->id)
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

        $history = HistoryBonusAgency::create([
            "store_id" => $request->store->id,
            "agency_id" => $agency->id,
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
