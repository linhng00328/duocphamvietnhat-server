<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Distribute;
use App\Models\InventoryEleDis;
use App\Models\InventoryHistory;
use App\Models\InventorySubDis;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralPhoneCustomer;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * @group  User/Báo cáo
 */
class ReportController extends Controller
{



    /**
     * Báo cáo doanh thu tổng quan
     * @urlParam  store_code required Store code
     * @queryParam  collaborator_by_customer_id
     * @queryParam  agency_by_customer_id
     * @queryParam  date_from
     * @queryParam  date_to
     * @queryParam  date_from_compare
     * @queryParam  date_to_compare
     * @queryParam branch_id int Branch_id chi nhánh
     * 
     */
    public function overview(Request $request)
    {
        function handle_data(Request $request, $dateFrom,  $dateTo)
        {
            $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

            $collaborator_by_customer_id = $request->get('collaborator_by_customer_id');
            $agency_by_customer_id = $request->get('agency_by_customer_id');
            $agency_ctv_by_customer_id = $request->get('agency_ctv_by_customer_id');

            if (str_contains($request->url(), 'collaborator/') == true) {
                $collaborator_by_customer_id  = $request->customer->id;
            }

            if (str_contains($request->url(), 'agency/') == true) {
                $agency_by_customer_id  = $request->customer->id;
            }

            if (str_contains($request->url(), 'agency_ctv/') == true) {
                $agency_ctv_by_customer_id  = $request->customer->id;
            }


            $non_user =    $collaborator_by_customer_id != null ||   $agency_by_customer_id != null ||  $agency_ctv_by_customer_id != null;

            $total_order_count = 0;
            $total_referral_of_customer_count = 0; //Danh sách người giới thiệu mới
            $total_collaborator_reg_count = 0;
            $total_shipping_fee = 0;
            $total_before_discount = 0;
            $combo_discount_amount = 0;
            $product_discount_amount = 0;
            $total_after_discount_no_bonus = 0;
            $voucher_discount_amount = 0;
            $total_after_discount = 0;
            $total_final = 0;
            $share_collaborator = 0;
            $details_by_order_status = [];
            $details_by_payment_status = [];
            $charts = [];

            //Config
            $carbon = Carbon::now('Asia/Ho_Chi_Minh');
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);



            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);


            //check loại charts
            $type = 'month';
            $date2Compare = clone $date2;

            if ($date2Compare->subDays(2) <= $date1) {

                $type = 'hour';
            } else 
            if ($date2Compare->subMonths(2) < $date1) {
                $type = 'day';
            } else 
            if ($date2Compare->subMonths(24) < $date1) {
                $type = 'month';
            }
            if ($date2->year - $date1->year > 2) {
                return new Exception(MsgCode::GREAT_TIME[1]);;
            }

            $allOrder = null;

            if ($collaborator_by_customer_id  != null) {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })
                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    ->where('collaborator_by_customer_id', $collaborator_by_customer_id)
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            } else if ($agency_by_customer_id != null) {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })
                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    ->where('agency_by_customer_id', $agency_by_customer_id)
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            } else if ($agency_ctv_by_customer_id != null) {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })
                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    ->where('agency_ctv_by_customer_id', $agency_ctv_by_customer_id)
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            } else {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })

                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            }

            $allCollaborator = Collaborator::where('store_id', $request->store->id)
                ->where('created_at', '>=',  $dateFrom)
                ->where('created_at', '<', $dateTo)
                ->get();

            $allReferral = [];

            if ($collaborator_by_customer_id != null) {
                $allReferral = ReferralPhoneCustomer::where('store_id', $request->store->id)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->where('customer_id', $collaborator_by_customer_id)
                    ->get();
            }


            $defineOrderStatus = StatusDefineCode::defineDataOrder(true);
            $definePaymentStatus = StatusDefineCode::defineDataPayment(true);

            //Đặt time charts
            if ($type == 'hour') {
                for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                    $charts[$i->format('Y-m-d H:00:00')] = [
                        'time' => $i->format('Y-m-d H:00:00'),
                        'total_order_count' => 0,
                        'total_shipping_fee' => 0,
                        'total_before_discount' => 0,
                        'combo_discount_amount' => 0,
                        'voucher_discount_amount' => 0,
                        'total_after_discount' => 0,
                        'total_after_discount_no_bonus' => 0,
                        'total_final' => 0,
                        'total_collaborator_reg_count' => 0,
                        'total_referral_of_customer_count' => 0,
                        'share_collaborator' => 0,
                    ];
                }
            }

            if ($type == 'day') {
                for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                    $charts[$i->format('Y-m-d')] = [
                        'time' => $i->format('Y-m-d'),
                        'total_order_count' => 0,
                        'total_shipping_fee' => 0,
                        'total_before_discount' => 0,
                        'combo_discount_amount' => 0,
                        'voucher_discount_amount' => 0,
                        'total_after_discount' => 0,
                        'total_after_discount_no_bonus' => 0,
                        'total_final' => 0,
                        'total_collaborator_reg_count' => 0,
                        'total_referral_of_customer_count' => 0,
                        'share_collaborator' => 0,
                    ];
                }
            }

            if ($type == 'month') {
                for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                    $charts[$i->format('Y-m')] = [
                        'time' => $i->format('Y-m'),
                        'total_order_count' => 0,
                        'total_shipping_fee' => 0,
                        'total_before_discount' => 0,
                        'combo_discount_amount' => 0,
                        'voucher_discount_amount' => 0,
                        'total_after_discount' => 0,
                        'total_after_discount_no_bonus' => 0,
                        'total_final' => 0,
                        'total_collaborator_reg_count' => 0,
                        'total_referral_of_customer_count' => 0,
                        'share_collaborator' => 0
                    ];
                }
            }
            $total_order_count_refunds = 0;
            foreach ($allOrder as $itemRow) {

                $total_order_count += 1;

                if (
                    $itemRow->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS &&
                    $itemRow->payment_status == StatusDefineCode::PAY_REFUNDS
                ) {
                    $total_order_count_refunds += 1;
                    $total_final -= $itemRow->total_final;
                }

                if (
                    ($itemRow->order_status == StatusDefineCode::COMPLETED || $itemRow->order_status == StatusDefineCode::RECEIVED_PRODUCT)   &&
                    $itemRow->payment_status == StatusDefineCode::PAID
                ) {
                    $total_final += $itemRow->total_final;
                    $total_after_discount_no_bonus += ($itemRow->total_before_discount - $itemRow->combo_discount_amount - $itemRow->product_discount_amount - $itemRow->voucher_discount_amount);
                }

                if ($itemRow->order_status != StatusDefineCode::CUSTOMER_CANCELLED && $itemRow->order_status != StatusDefineCode::CUSTOMER_HAS_RETURNS) {


                    $share_collaborator += $itemRow->share_collaborator;


                    $total_shipping_fee += $itemRow->total_shipping_fee;
                    $total_before_discount += $itemRow->total_before_discount;
                    $combo_discount_amount += $itemRow->combo_discount_amount;
                    $product_discount_amount += $itemRow->product_discount_amount;
                    $voucher_discount_amount += $itemRow->voucher_discount_amount;
                    $total_after_discount += $itemRow->total_after_discount;
                }

                // else if ($itemRow->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {

                //     $total_final -= $itemRow->total_final;
                //     $total_after_discount_no_bonus -= ($itemRow->total_before_discount - $itemRow->combo_discount_amount - $itemRow->product_discount_amount - $itemRow->voucher_discount_amount);
                //     $share_collaborator += $itemRow->share_collaborator;
                // }



                //Định hình charts
                $time = $carbon->parse($itemRow->created_at);

                $time_compare = "xxx";
                if ($type == 'hour') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day . ' ' . $time->hour . ':00:00';
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d H:00:00');
                }
                if ($type == 'day') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d');
                }
                if ($type == 'month') {
                    $time_compare = $time->year . '-' . $time->month;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m');
                }




                if (isset($charts[$time_compare])) {

                    $charts[$time_compare]["total_order_count"] = ($charts[$time_compare]["total_order_count"] ?? 0) + 1;

                    // $charts[$time_compare]["total_shipping_fee"] = ($charts[$time_compare]["total_shipping_fee"] ?? 0)  + $itemRow->total_shipping_fee;
                    // $charts[$time_compare]["total_before_discount"] = ($charts[$time_compare]["total_before_discount"] ?? 0) +  $itemRow->total_before_discount;
                    // $charts[$time_compare]["combo_discount_amount"] = ($charts[$time_compare]["combo_discount_amount"] ?? 0) +  $itemRow->combo_discount_amount;
                    // $charts[$time_compare]["voucher_discount_amount"] =  ($charts[$time_compare]["voucher_discount_amount"] ?? 0) +  $itemRow->voucher_discount_amount;
                    // $charts[$time_compare]["total_after_discount"] = ($charts[$time_compare]["total_after_discount"] ?? 0) +  $itemRow->total_after_discount;

                    if (
                        $itemRow->order_status == StatusDefineCode::COMPLETED
                        &&
                        $itemRow->payment_status == StatusDefineCode::PAID

                    ) {
                        $charts[$time_compare]["total_final"] =  ($charts[$time_compare]["total_final"] ?? 0) +  $itemRow->total_final;
                        $charts[$time_compare]["total_after_discount_no_bonus"] =  ($charts[$time_compare]["total_after_discount_no_bonus"] ?? 0)  + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $charts[$time_compare]["share_collaborator"] = ($charts[$time_compare]["share_collaborator"] ?? 0) + 1;
                    }



                    // if ($itemRow->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                    //     $charts[$time_compare]["total_after_discount_no_bonus"] =  ($charts[$time_compare]["total_after_discount_no_bonus"] ?? 0) - ($itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount);
                    //     $charts[$time_compare]["total_final"] =  ($charts[$time_compare]["total_final"] ?? 0) -  $itemRow->total_final;
                    // }
                }
                /////////////////
                foreach ($defineOrderStatus as $status) {
                    // [0, "WAITING_FOR_PROGRESSING", "Chờ xử lý"]
                    if ($itemRow->order_status == $status[0]) {
                        $details_by_order_status[$status[1]]["name"] = $status[2];

                        $details_by_order_status[$status[1]]["total_shipping_fee"] = ($details_by_order_status[$status[1]]["total_shipping_fee"] ?? 0) + $itemRow->total_shipping_fee;
                        $details_by_order_status[$status[1]]["total_before_discount"] = ($details_by_order_status[$status[1]]["total_before_discount"] ?? 0) + $itemRow->total_before_discount;
                        $details_by_order_status[$status[1]]["combo_discount_amount"] = ($details_by_order_status[$status[1]]["combo_discount_amount"] ?? 0) + $itemRow->combo_discount_amount;
                        $details_by_order_status[$status[1]]["voucher_discount_amount"] = ($details_by_order_status[$status[1]]["voucher_discount_amount"] ?? 0) + $itemRow->voucher_discount_amount;
                        $details_by_order_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_order_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_order_status[$status[1]]["total_after_discount"] = ($details_by_order_status[$status[1]]["total_after_discount"] ?? 0) + $itemRow->total_after_discount;
                        $details_by_order_status[$status[1]]["total_final"] = ($details_by_order_status[$status[1]]["total_final"] ?? 0) + $itemRow->total_final;
                        $details_by_order_status[$status[1]]["total_order_count"] = ($details_by_order_status[$status[1]]["total_order_count"] ?? 0) + 1;
                        $details_by_order_status[$status[1]]["share_collaborator"] = ($details_by_order_status[$status[1]]["share_collaborator"] ?? 0) + $itemRow->share_collaborator;
                    } else {
                        $details_by_order_status[$status[1]]["name"] = $status[2];

                        $details_by_order_status[$status[1]]["total_shipping_fee"] = ($details_by_order_status[$status[1]]["total_shipping_fee"] ?? 0);
                        $details_by_order_status[$status[1]]["total_before_discount"] = ($details_by_order_status[$status[1]]["total_before_discount"] ?? 0);
                        $details_by_order_status[$status[1]]["combo_discount_amount"] = ($details_by_order_status[$status[1]]["combo_discount_amount"] ?? 0);
                        $details_by_order_status[$status[1]]["voucher_discount_amount"] = ($details_by_order_status[$status[1]]["voucher_discount_amount"] ?? 0);
                        $details_by_order_status[$status[1]]["total_after_discount"] = ($details_by_order_status[$status[1]]["total_after_discount"] ?? 0);
                        $details_by_order_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_order_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_order_status[$status[1]]["total_final"] = ($details_by_order_status[$status[1]]["total_final"] ?? 0);
                        $details_by_order_status[$status[1]]["total_order_count"] = ($details_by_order_status[$status[1]]["total_order_count"] ?? 0);
                        $details_by_order_status[$status[1]]["share_collaborator"] = ($details_by_order_status[$status[1]]["share_collaborator"] ?? 0);
                    }
                }

                foreach ($definePaymentStatus as $status) {
                    // [0, "WAITING_FOR_PROGRESSING", "Chờ xử lý"]
                    if ($itemRow->payment_status == $status[0]) {
                        $details_by_payment_status[$status[1]]["name"] = $status[2];

                        $details_by_payment_status[$status[1]]["total_shipping_fee"] = ($details_by_payment_status[$status[1]]["total_shipping_fee"] ?? 0) + $itemRow->total_shipping_fee;
                        $details_by_payment_status[$status[1]]["total_before_discount"] = ($details_by_payment_status[$status[1]]["total_before_discount"] ?? 0) + $itemRow->total_before_discount;
                        $details_by_payment_status[$status[1]]["combo_discount_amount"] = ($details_by_payment_status[$status[1]]["combo_discount_amount"] ?? 0) + $itemRow->combo_discount_amount;
                        $details_by_payment_status[$status[1]]["voucher_discount_amount"] = ($details_by_payment_status[$status[1]]["voucher_discount_amount"] ?? 0) + $itemRow->voucher_discount_amount;
                        $details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_payment_status[$status[1]]["total_after_discount"] = ($details_by_payment_status[$status[1]]["total_after_discount"] ?? 0) + $itemRow->total_after_discount;
                        $details_by_payment_status[$status[1]]["total_final"] = ($details_by_payment_status[$status[1]]["total_final"] ?? 0) + $itemRow->total_final;
                        $details_by_payment_status[$status[1]]["total_order_count"] = ($details_by_payment_status[$status[1]]["total_order_count"] ?? 0) + 1;
                        $details_by_payment_status[$status[1]]["share_collaborator"] = ($details_by_payment_status[$status[1]]["share_collaborator"] ?? 0) + $itemRow->share_collaborator;
                    } else {
                        $details_by_payment_status[$status[1]]["name"] = $status[2];

                        $details_by_payment_status[$status[1]]["total_shipping_fee"] = ($details_by_payment_status[$status[1]]["total_shipping_fee"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_before_discount"] = ($details_by_payment_status[$status[1]]["total_before_discount"] ?? 0);
                        $details_by_payment_status[$status[1]]["combo_discount_amount"] = ($details_by_payment_status[$status[1]]["combo_discount_amount"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_payment_status[$status[1]]["voucher_discount_amount"] = ($details_by_payment_status[$status[1]]["voucher_discount_amount"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_after_discount"] = ($details_by_payment_status[$status[1]]["total_after_discount"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_final"] = ($details_by_payment_status[$status[1]]["total_final"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_order_count"] = ($details_by_payment_status[$status[1]]["total_order_count"] ?? 0);
                        $details_by_payment_status[$status[1]]["share_collaborator"] = ($details_by_payment_status[$status[1]]["share_collaborator"] ?? 0);
                    }
                }
            }


            //Danh sách cộng tác viên mới
            foreach ($allCollaborator as $itemRow) {
                $total_collaborator_reg_count += 1;

                //Định hình charts
                $time = $carbon->parse($itemRow->created_at);

                $time_compare = "xxx";
                if ($type == 'hour') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day . ' ' . $time->hour . ':00:00';
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d H:00:00');
                }
                if ($type == 'day') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d');
                }
                if ($type == 'month') {
                    $time_compare = $time->year . '-' . $time->month;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m');
                }


                if (isset($charts[$time_compare])) {
                    $charts[$time_compare]["total_collaborator_reg_count"] = ($charts[$time_compare]["total_collaborator_reg_count"] ?? 0) + 1;
                }
            }

            //Danh sách người giới thiệu mới
            foreach ($allReferral  as $itemRow) {
                $total_referral_of_customer_count += 1;

                //Định hình charts
                $time = $carbon->parse($itemRow->created_at);

                $time_compare = "xxx";
                if ($type == 'hour') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day . ' ' . $time->hour . ':00:00';
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d H:00:00');
                }
                if ($type == 'day') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d');
                }
                if ($type == 'month') {
                    $time_compare = $time->year . '-' . $time->month;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m');
                }


                if (isset($charts[$time_compare])) {
                    $charts[$time_compare]["total_referral_of_customer_count"] = ($charts[$time_compare]["total_referral_of_customer_count"] ?? 0) + 1;
                }
            }

            $charts = array_values($charts);
            // dd($total_final);



            $data = [
                'total_referral_of_customer_count' => $total_referral_of_customer_count,
                'total_collaborator_reg_count' => $total_collaborator_reg_count ?? 0,
                'total_order_count' => $total_order_count,
                'total_shipping_fee' => $total_shipping_fee,
                'total_before_discount' => $total_before_discount,
                'combo_discount_amount' => $combo_discount_amount,
                'product_discount_amount' => $product_discount_amount,
                'voucher_discount_amount' => $voucher_discount_amount,
                'total_after_discount_no_bonus' => $total_after_discount_no_bonus,
                'total_after_discount' => $total_after_discount,
                'total_final' => $total_final ?? 0,
                'total_order_count_refunds' => $total_order_count_refunds,
                'share_collaborator' => $share_collaborator,
                'details_by_order_status' => count($details_by_order_status) == 0 ? null : $details_by_order_status,
                'details_by_payment_status' => count($details_by_payment_status) == 0 ? null : $details_by_payment_status,
                'type_chart' => $type,
                'charts' => $charts

            ];

            return $data;
        }

        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $data_prime_time = handle_data($request, $dateFrom, $dateTo);
        if ($data_prime_time instanceof Exception) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => $data_prime_time->getMessage(),
            ], 400);
        }

        $dateFromCompare = request('date_from_compare');
        $dateToCompare = request('date_to_compare');

        $data_compare_time = null;
        if ($dateFromCompare != null && $dateToCompare != null) {
            $data_compare_time = handle_data($request, $dateFromCompare, $dateToCompare);

            if ($data_compare_time instanceof Exception) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $data_compare_time->getMessage(),
                ], 400);
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'data_prime_time' => $data_prime_time,
                'data_compare_time' => $data_compare_time
            ]
        ], 200);
    }

    public function overviewV1(Request $request)
    {
        function handle_dataV1(Request $request, $dateFrom,  $dateTo)
        {
            $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

            $collaborator_by_customer_id = $request->get('collaborator_by_customer_id');
            $agency_by_customer_id = $request->get('agency_by_customer_id');
            $agency_ctv_by_customer_id = $request->get('agency_ctv_by_customer_id');

            if (str_contains($request->url(), 'collaborator/') == true) {
                $collaborator_by_customer_id  = $request->customer->id;
            }

            if (str_contains($request->url(), 'agency/') == true) {
                $agency_by_customer_id  = $request->customer->id;
            }

            if (str_contains($request->url(), 'agency_ctv/') == true) {
                $agency_ctv_by_customer_id  = $request->customer->id;
            }


            $non_user =    $collaborator_by_customer_id != null ||   $agency_by_customer_id != null ||  $agency_ctv_by_customer_id != null;

            $total_order_count = 0;
            $total_referral_of_customer_count = 0; //Danh sách người giới thiệu mới
            $total_collaborator_reg_count = 0;
            $total_shipping_fee = 0;
            $total_before_discount = 0;
            $combo_discount_amount = 0;
            $product_discount_amount = 0;
            $total_after_discount_no_bonus = 0;
            $voucher_discount_amount = 0;
            $total_after_discount = 0;
            $total_final = 0;
            $share_collaborator = 0;
            $details_by_order_status = [];
            $details_by_payment_status = [];
            $charts = [];

            //Config
            $carbon = Carbon::now('Asia/Ho_Chi_Minh');
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);


            $dateFrom .= ' 00:00:00';
            $dateTo .= ' 23:59:59';

            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            //check loại charts
            $type = 'month';
            $date2Compare = clone $date2;

            if ($date2Compare->subDays(2) <= $date1) {

                $type = 'hour';
            } else 
            if ($date2Compare->subMonths(2) < $date1) {
                $type = 'day';
            } else 
            if ($date2Compare->subMonths(24) < $date1) {
                $type = 'month';
            }
            if ($date2->year - $date1->year > 2) {
                return new Exception(MsgCode::GREAT_TIME[1]);;
            }
            $allOrder = null;

            if ($collaborator_by_customer_id  != null) {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('completed_at', '>=',  $dateFrom)
                    ->where('completed_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })
                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    ->where('collaborator_by_customer_id', $collaborator_by_customer_id)
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            } else if ($agency_by_customer_id != null) {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('completed_at', '>=',  $dateFrom)
                    ->where('completed_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })
                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    ->where('agency_by_customer_id', $agency_by_customer_id)
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            } else if ($agency_ctv_by_customer_id != null) {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('completed_at', '>=',  $dateFrom)
                    ->where('completed_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })
                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    ->where('agency_ctv_by_customer_id', $agency_ctv_by_customer_id)
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            } else {
                $allOrder = Order::where('store_id', $request->store->id)
                    ->where('completed_at', '>=',  $dateFrom)
                    ->where('completed_at', '<', $dateTo)
                    ->when(request('branch_id') != null, function ($query) {
                        $query->where('branch_id', request('branch_id'));
                    })

                    ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                        $query->whereIn('branch_id', $branch_ids);
                    })
                    // ->where('order_status', StatusDefineCode::COMPLETED)
                    // ->where('payment_status', StatusDefineCode::PAID)
                    ->get();
            }
            $allCollaborator = Collaborator::where('store_id', $request->store->id)
                ->where('created_at', '>=',  $dateFrom)
                ->where('created_at', '<', $dateTo)
                ->get();

            $allReferral = [];

            if ($collaborator_by_customer_id != null) {
                $allReferral = ReferralPhoneCustomer::where('store_id', $request->store->id)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->where('customer_id', $collaborator_by_customer_id)
                    ->get();
            }

            $defineOrderStatus = StatusDefineCode::defineDataOrder(true);
            $definePaymentStatus = StatusDefineCode::defineDataPayment(true);

            //Đặt time charts
            if ($type == 'hour') {
                for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                    $charts[$i->format('Y-m-d H:00:00')] = [
                        'time' => $i->format('Y-m-d H:00:00'),
                        'total_order_count' => 0,
                        'total_shipping_fee' => 0,
                        'total_before_discount' => 0,
                        'combo_discount_amount' => 0,
                        'voucher_discount_amount' => 0,
                        'total_after_discount' => 0,
                        'total_after_discount_no_bonus' => 0,
                        'total_final' => 0,
                        'total_collaborator_reg_count' => 0,
                        'total_referral_of_customer_count' => 0,
                        'share_collaborator' => 0,
                    ];
                }
            }

            if ($type == 'day') {
                for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                    $charts[$i->format('Y-m-d')] = [
                        'time' => $i->format('Y-m-d'),
                        'total_order_count' => 0,
                        'total_shipping_fee' => 0,
                        'total_before_discount' => 0,
                        'combo_discount_amount' => 0,
                        'voucher_discount_amount' => 0,
                        'total_after_discount' => 0,
                        'total_after_discount_no_bonus' => 0,
                        'total_final' => 0,
                        'total_collaborator_reg_count' => 0,
                        'total_referral_of_customer_count' => 0,
                        'share_collaborator' => 0,
                    ];
                }
            }

            if ($type == 'month') {
                for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                    $charts[$i->format('Y-m')] = [
                        'time' => $i->format('Y-m'),
                        'total_order_count' => 0,
                        'total_shipping_fee' => 0,
                        'total_before_discount' => 0,
                        'combo_discount_amount' => 0,
                        'voucher_discount_amount' => 0,
                        'total_after_discount' => 0,
                        'total_after_discount_no_bonus' => 0,
                        'total_final' => 0,
                        'total_collaborator_reg_count' => 0,
                        'total_referral_of_customer_count' => 0,
                        'share_collaborator' => 0
                    ];
                }
            }
            $total_order_count_refunds = 0;
            foreach ($allOrder as $itemRow) {

                $total_order_count += 1;

                if (
                    $itemRow->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS &&
                    $itemRow->payment_status == StatusDefineCode::PAY_REFUNDS
                ) {
                    $total_order_count_refunds += 1;
                    $total_final -= $itemRow->total_final;
                }

                if (
                    ($itemRow->order_status == StatusDefineCode::COMPLETED || $itemRow->order_status == StatusDefineCode::RECEIVED_PRODUCT)   &&
                    $itemRow->payment_status == StatusDefineCode::PAID
                ) {
                    $total_final += $itemRow->total_final;
                    $total_after_discount_no_bonus += ($itemRow->total_before_discount - $itemRow->combo_discount_amount - $itemRow->product_discount_amount - $itemRow->voucher_discount_amount);
                }

                if ($itemRow->order_status != StatusDefineCode::CUSTOMER_CANCELLED && $itemRow->order_status != StatusDefineCode::CUSTOMER_HAS_RETURNS) {


                    $share_collaborator += $itemRow->share_collaborator;


                    $total_shipping_fee += $itemRow->total_shipping_fee;
                    $total_before_discount += $itemRow->total_before_discount;
                    $combo_discount_amount += $itemRow->combo_discount_amount;
                    $product_discount_amount += $itemRow->product_discount_amount;
                    $voucher_discount_amount += $itemRow->voucher_discount_amount;
                    $total_after_discount += $itemRow->total_after_discount;
                }

                // else if ($itemRow->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {

                //     $total_final -= $itemRow->total_final;
                //     $total_after_discount_no_bonus -= ($itemRow->total_before_discount - $itemRow->combo_discount_amount - $itemRow->product_discount_amount - $itemRow->voucher_discount_amount);
                //     $share_collaborator += $itemRow->share_collaborator;
                // }



                //Định hình charts
                $time = $carbon->parse($itemRow->completed_at);

                $time_compare = "xxx";
                if ($type == 'hour') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day . ' ' . $time->hour . ':00:00';
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d H:00:00');
                }
                if ($type == 'day') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d');
                }
                if ($type == 'month') {
                    $time_compare = $time->year . '-' . $time->month;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m');
                }




                if (isset($charts[$time_compare])) {

                    $charts[$time_compare]["total_order_count"] = ($charts[$time_compare]["total_order_count"] ?? 0) + 1;

                    // $charts[$time_compare]["total_shipping_fee"] = ($charts[$time_compare]["total_shipping_fee"] ?? 0)  + $itemRow->total_shipping_fee;
                    // $charts[$time_compare]["total_before_discount"] = ($charts[$time_compare]["total_before_discount"] ?? 0) +  $itemRow->total_before_discount;
                    // $charts[$time_compare]["combo_discount_amount"] = ($charts[$time_compare]["combo_discount_amount"] ?? 0) +  $itemRow->combo_discount_amount;
                    // $charts[$time_compare]["voucher_discount_amount"] =  ($charts[$time_compare]["voucher_discount_amount"] ?? 0) +  $itemRow->voucher_discount_amount;
                    // $charts[$time_compare]["total_after_discount"] = ($charts[$time_compare]["total_after_discount"] ?? 0) +  $itemRow->total_after_discount;

                    if (
                        $itemRow->order_status == StatusDefineCode::COMPLETED
                        &&
                        $itemRow->payment_status == StatusDefineCode::PAID

                    ) {
                        $charts[$time_compare]["total_final"] =  ($charts[$time_compare]["total_final"] ?? 0) +  $itemRow->total_final;
                        $charts[$time_compare]["total_after_discount_no_bonus"] =  ($charts[$time_compare]["total_after_discount_no_bonus"] ?? 0)  + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $charts[$time_compare]["share_collaborator"] = ($charts[$time_compare]["share_collaborator"] ?? 0) + 1;
                    }



                    // if ($itemRow->order_status == StatusDefineCode::CUSTOMER_HAS_RETURNS) {
                    //     $charts[$time_compare]["total_after_discount_no_bonus"] =  ($charts[$time_compare]["total_after_discount_no_bonus"] ?? 0) - ($itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount);
                    //     $charts[$time_compare]["total_final"] =  ($charts[$time_compare]["total_final"] ?? 0) -  $itemRow->total_final;
                    // }
                }
                /////////////////
                foreach ($defineOrderStatus as $status) {
                    // [0, "WAITING_FOR_PROGRESSING", "Chờ xử lý"]
                    if ($itemRow->order_status == $status[0]) {
                        $details_by_order_status[$status[1]]["name"] = $status[2];

                        $details_by_order_status[$status[1]]["total_shipping_fee"] = ($details_by_order_status[$status[1]]["total_shipping_fee"] ?? 0) + $itemRow->total_shipping_fee;
                        $details_by_order_status[$status[1]]["total_before_discount"] = ($details_by_order_status[$status[1]]["total_before_discount"] ?? 0) + $itemRow->total_before_discount;
                        $details_by_order_status[$status[1]]["combo_discount_amount"] = ($details_by_order_status[$status[1]]["combo_discount_amount"] ?? 0) + $itemRow->combo_discount_amount;
                        $details_by_order_status[$status[1]]["voucher_discount_amount"] = ($details_by_order_status[$status[1]]["voucher_discount_amount"] ?? 0) + $itemRow->voucher_discount_amount;
                        $details_by_order_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_order_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_order_status[$status[1]]["total_after_discount"] = ($details_by_order_status[$status[1]]["total_after_discount"] ?? 0) + $itemRow->total_after_discount;
                        $details_by_order_status[$status[1]]["total_final"] = ($details_by_order_status[$status[1]]["total_final"] ?? 0) + $itemRow->total_final;
                        $details_by_order_status[$status[1]]["total_order_count"] = ($details_by_order_status[$status[1]]["total_order_count"] ?? 0) + 1;
                        $details_by_order_status[$status[1]]["share_collaborator"] = ($details_by_order_status[$status[1]]["share_collaborator"] ?? 0) + $itemRow->share_collaborator;
                    } else {
                        $details_by_order_status[$status[1]]["name"] = $status[2];

                        $details_by_order_status[$status[1]]["total_shipping_fee"] = ($details_by_order_status[$status[1]]["total_shipping_fee"] ?? 0);
                        $details_by_order_status[$status[1]]["total_before_discount"] = ($details_by_order_status[$status[1]]["total_before_discount"] ?? 0);
                        $details_by_order_status[$status[1]]["combo_discount_amount"] = ($details_by_order_status[$status[1]]["combo_discount_amount"] ?? 0);
                        $details_by_order_status[$status[1]]["voucher_discount_amount"] = ($details_by_order_status[$status[1]]["voucher_discount_amount"] ?? 0);
                        $details_by_order_status[$status[1]]["total_after_discount"] = ($details_by_order_status[$status[1]]["total_after_discount"] ?? 0);
                        $details_by_order_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_order_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_order_status[$status[1]]["total_final"] = ($details_by_order_status[$status[1]]["total_final"] ?? 0);
                        $details_by_order_status[$status[1]]["total_order_count"] = ($details_by_order_status[$status[1]]["total_order_count"] ?? 0);
                        $details_by_order_status[$status[1]]["share_collaborator"] = ($details_by_order_status[$status[1]]["share_collaborator"] ?? 0);
                    }
                }

                foreach ($definePaymentStatus as $status) {
                    // [0, "WAITING_FOR_PROGRESSING", "Chờ xử lý"]
                    if ($itemRow->payment_status == $status[0]) {
                        $details_by_payment_status[$status[1]]["name"] = $status[2];

                        $details_by_payment_status[$status[1]]["total_shipping_fee"] = ($details_by_payment_status[$status[1]]["total_shipping_fee"] ?? 0) + $itemRow->total_shipping_fee;
                        $details_by_payment_status[$status[1]]["total_before_discount"] = ($details_by_payment_status[$status[1]]["total_before_discount"] ?? 0) + $itemRow->total_before_discount;
                        $details_by_payment_status[$status[1]]["combo_discount_amount"] = ($details_by_payment_status[$status[1]]["combo_discount_amount"] ?? 0) + $itemRow->combo_discount_amount;
                        $details_by_payment_status[$status[1]]["voucher_discount_amount"] = ($details_by_payment_status[$status[1]]["voucher_discount_amount"] ?? 0) + $itemRow->voucher_discount_amount;
                        $details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_payment_status[$status[1]]["total_after_discount"] = ($details_by_payment_status[$status[1]]["total_after_discount"] ?? 0) + $itemRow->total_after_discount;
                        $details_by_payment_status[$status[1]]["total_final"] = ($details_by_payment_status[$status[1]]["total_final"] ?? 0) + $itemRow->total_final;
                        $details_by_payment_status[$status[1]]["total_order_count"] = ($details_by_payment_status[$status[1]]["total_order_count"] ?? 0) + 1;
                        $details_by_payment_status[$status[1]]["share_collaborator"] = ($details_by_payment_status[$status[1]]["share_collaborator"] ?? 0) + $itemRow->share_collaborator;
                    } else {
                        $details_by_payment_status[$status[1]]["name"] = $status[2];

                        $details_by_payment_status[$status[1]]["total_shipping_fee"] = ($details_by_payment_status[$status[1]]["total_shipping_fee"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_before_discount"] = ($details_by_payment_status[$status[1]]["total_before_discount"] ?? 0);
                        $details_by_payment_status[$status[1]]["combo_discount_amount"] = ($details_by_payment_status[$status[1]]["combo_discount_amount"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] = ($details_by_payment_status[$status[1]]["total_after_discount_no_bonus"] ?? 0) + $itemRow->total_before_discount -  $itemRow->combo_discount_amount - $itemRow->voucher_discount_amount - $itemRow->product_discount_amount;
                        $details_by_payment_status[$status[1]]["voucher_discount_amount"] = ($details_by_payment_status[$status[1]]["voucher_discount_amount"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_after_discount"] = ($details_by_payment_status[$status[1]]["total_after_discount"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_final"] = ($details_by_payment_status[$status[1]]["total_final"] ?? 0);
                        $details_by_payment_status[$status[1]]["total_order_count"] = ($details_by_payment_status[$status[1]]["total_order_count"] ?? 0);
                        $details_by_payment_status[$status[1]]["share_collaborator"] = ($details_by_payment_status[$status[1]]["share_collaborator"] ?? 0);
                    }
                }
            }


            //Danh sách cộng tác viên mới
            foreach ($allCollaborator as $itemRow) {
                $total_collaborator_reg_count += 1;

                //Định hình charts
                $time = $carbon->parse($itemRow->created_at);

                $time_compare = "xxx";
                if ($type == 'hour') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day . ' ' . $time->hour . ':00:00';
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d H:00:00');
                }
                if ($type == 'day') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d');
                }
                if ($type == 'month') {
                    $time_compare = $time->year . '-' . $time->month;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m');
                }


                if (isset($charts[$time_compare])) {
                    $charts[$time_compare]["total_collaborator_reg_count"] = ($charts[$time_compare]["total_collaborator_reg_count"] ?? 0) + 1;
                }
            }

            //Danh sách người giới thiệu mới
            foreach ($allReferral  as $itemRow) {
                $total_referral_of_customer_count += 1;

                //Định hình charts
                $time = $carbon->parse($itemRow->created_at);

                $time_compare = "xxx";
                if ($type == 'hour') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day . ' ' . $time->hour . ':00:00';
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d H:00:00');
                }
                if ($type == 'day') {
                    $time_compare = $time->year . '-' . $time->month . '-' . $time->day;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m-d');
                }
                if ($type == 'month') {
                    $time_compare = $time->year . '-' . $time->month;
                    $time_compare = $carbon->parse($time_compare);
                    $time_compare = $time_compare->format('Y-m');
                }


                if (isset($charts[$time_compare])) {
                    $charts[$time_compare]["total_referral_of_customer_count"] = ($charts[$time_compare]["total_referral_of_customer_count"] ?? 0) + 1;
                }
            }

            $charts = array_values($charts);
            // dd($total_final);



            $data = [
                'total_referral_of_customer_count' => $total_referral_of_customer_count,
                'total_collaborator_reg_count' => $total_collaborator_reg_count ?? 0,
                'total_order_count' => $total_order_count,
                'total_shipping_fee' => $total_shipping_fee,
                'total_before_discount' => $total_before_discount,
                'combo_discount_amount' => $combo_discount_amount,
                'product_discount_amount' => $product_discount_amount,
                'voucher_discount_amount' => $voucher_discount_amount,
                'total_after_discount_no_bonus' => $total_after_discount_no_bonus,
                'total_after_discount' => $total_after_discount,
                'total_final' => $total_final ?? 0,
                'total_order_count_refunds' => $total_order_count_refunds,
                'share_collaborator' => $share_collaborator,
                'details_by_order_status' => count($details_by_order_status) == 0 ? null : $details_by_order_status,
                'details_by_payment_status' => count($details_by_payment_status) == 0 ? null : $details_by_payment_status,
                'type_chart' => $type,
                'charts' => $charts

            ];

            return $data;
        }

        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $data_prime_time = handle_dataV1($request, $dateFrom, $dateTo);
        if ($data_prime_time instanceof Exception) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => $data_prime_time->getMessage(),
            ], 400);
        }

        $dateFromCompare = request('date_from_compare');
        $dateToCompare = request('date_to_compare');

        $data_compare_time = null;
        if ($dateFromCompare != null && $dateToCompare != null) {
            $data_compare_time = handle_dataV1($request, $dateFromCompare, $dateToCompare);

            if ($data_compare_time instanceof Exception) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => $data_compare_time->getMessage(),
                ], 400);
            }
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'data_prime_time' => $data_prime_time,
                'data_compare_time' => $data_compare_time
            ]
        ], 200);
    }


    /**
     * Báo cáo top sản phẩm
     * @urlParam  store_code required Store code
     * @queryParam  collaborator_by_customer_id
     * @queryParam  agency_by_customer_id
     * @queryParam  date_from
     * @queryParam  date_to
     * @queryParam  date_from_compare
     * @queryParam  date_to_compare
     * @queryParam branch_id int Branch_id chi nhánh
     * 
     */
    public function top_ten_products(Request $request)
    {

        $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        ///////////////// topByTotalItems
        $topByTotalItems = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'line_items',
                'line_items.product_id',
                '=',
                'products.id'
            )
            ->where('line_items.is_refund', false)
            ->whereDate('line_items.created_at', '>=',  $dateFrom)
            ->whereDate('line_items.created_at', '<', $dateTo)
            ->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'line_items.branch_id',
                    $request->branch->id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('line_items.branch_id', $branch_ids);
            })
            ->select(
                'products.id',
                'products.name',
                'line_items.product_id',
                DB::raw('SUM(line_items.quantity) as total')
            )

            ->groupBy(
                'products.id',
                'line_items.product_id',
                'products.name'
            )

            ->orderBy('total', 'desc')->limit(10)->get();

        //
        $topByTotalItemsRefund = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'line_items',
                'line_items.product_id',
                '=',
                'products.id'
            )
            ->whereDate('line_items.created_at', '>=',  $dateFrom)
            ->whereDate('line_items.created_at', '<', $dateTo)
            ->where('line_items.is_refund', true)
            ->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'line_items.branch_id',
                    $request->branch->id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('line_items.branch_id', $branch_ids);
            })
            ->select(
                'products.id',
                'products.name',
                'line_items.product_id',
                DB::raw('SUM(line_items.quantity) as total')
            )

            ->groupBy(
                'products.id',
                'line_items.product_id',
                'products.name'
            )

            ->orderBy('total', 'desc')->limit(10)->get();

        //total refund
        $data_refund = [];

        foreach ($topByTotalItemsRefund as $topByTotalItem) {
            $data_refund[$topByTotalItem->id] = (int)$topByTotalItem->total;
        }

        $productsTop = Product::where('store_id', $request->store->id)->whereIn(
            'id',
            $topByTotalItems->pluck("id")
        )->get();

        $dataRTByTotal = [];


        foreach ($topByTotalItems as $topByTotalItem) {

            array_push($dataRTByTotal,  [
                "total_items" => (int)$topByTotalItem->total - ($data_refund[$topByTotalItem->id] ?? 0),
                "product" => $productsTop->where("id", $topByTotalItem->id)->first(),
            ]);
        }

        usort($dataRTByTotal, function ($a, $b) {
            return $a['total_items'] < $b['total_items'] ? 1 : 0;
        });


        ///////////////// topByNumberOfOrderItems  //
        $topByNumberOfOrderItems = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'line_items',
                'line_items.product_id',
                '=',
                'products.id'
            )
            ->leftJoin(
                'orders',
                'orders.id',
                '=',
                'line_items.order_id'
            )->where("orders.order_status", StatusDefineCode::COMPLETED)
            ->where('line_items.is_refund', false)
            ->whereDate('line_items.created_at', '>=',  $dateFrom)
            ->whereDate('line_items.created_at', '<', $dateTo)
            ->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'line_items.branch_id',
                    $request->branch->id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('line_items.branch_id', $branch_ids);
            })

            ->select(
                'products.id',
                'products.name',
                'line_items.product_id',
                DB::raw('COUNT(line_items.id) as total')
            )
            ->groupBy(
                'products.id',
                'line_items.product_id',
                'products.name'
            )->orderBy('total', 'desc')->limit(10)->get();
        //

        $topByNumberOfOrderItemsRefund = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'line_items',
                'line_items.product_id',
                '=',
                'products.id'
            )
            ->whereDate('line_items.created_at', '>=',  $dateFrom)
            ->whereDate('line_items.created_at', '<', $dateTo)
            ->where('line_items.is_refund', true)
            ->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'line_items.branch_id',
                    $request->branch->id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('line_items.branch_id', $branch_ids);
            })
            ->select(
                'products.id',
                'products.name',
                'line_items.product_id',
                DB::raw('COUNT(line_items.id) as total')
            )
            ->groupBy(
                'products.id',
                'line_items.product_id',
                'products.name'
            )->orderBy('total', 'desc')->limit(10)->get();

        //total_refund
        $data_refund = [];
        foreach ($topByNumberOfOrderItemsRefund as $topByNumberOfOrderItem) {
            $data_refund[$topByNumberOfOrderItem->id] = $topByNumberOfOrderItem->total;
        }


        $productsTop = Product::where('store_id', $request->store->id)->whereIn(
            'id',
            $topByNumberOfOrderItems->pluck("id")
        )->get();

        $dataRTByNumberOfOrder = [];

        foreach ($topByNumberOfOrderItems as $topByNumberOfOrderItem) {
            array_push($dataRTByNumberOfOrder,  [
                "number_of_orders" => $topByNumberOfOrderItem->total - ($data_refund[$topByNumberOfOrderItem->id] ?? 0),
                "product" => $productsTop->where("id", $topByNumberOfOrderItem->id)->first(),
            ]);
        }

        usort($dataRTByNumberOfOrder, function ($a, $b) {
            return $a['number_of_orders'] < $b['number_of_orders'] ? 1 : 0;
        });

        ///////////////// topByPriceItems
        $topByPriceItems = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'line_items',
                'line_items.product_id',
                '=',
                'products.id'
            )
            ->leftJoin(
                'orders',
                'orders.id',
                '=',
                'line_items.order_id'
            )->where("orders.order_status", StatusDefineCode::COMPLETED)
            ->whereDate('line_items.created_at', '>=',  $dateFrom)
            ->whereDate('line_items.created_at', '<', $dateTo)
            ->where('line_items.is_refund', false)
            ->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'line_items.branch_id',
                    $request->branch->id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('line_items.branch_id', $branch_ids);
            })
            ->select(
                'products.id',
                'products.name',
                'line_items.product_id',
                DB::raw('SUM(products.price*line_items.quantity) as total')
            )->groupBy(
                'products.id',
                'line_items.product_id',
                'products.name'
            )->orderBy('total', 'desc')->limit(10)->get();


        $topByPriceItemsRefund = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'line_items',
                'line_items.product_id',
                '=',
                'products.id'
            )->whereDate('line_items.created_at', '>=',  $dateFrom)
            ->whereDate('line_items.created_at', '<', $dateTo)
            ->where('line_items.is_refund', true)
            ->when($request->branch != null, function ($query) use ($request) {
                $query->where(
                    'line_items.branch_id',
                    $request->branch->id
                );
            })
            ->when(count($branch_ids) > 0, function ($query) use ($branch_ids) {
                $query->whereIn('line_items.branch_id', $branch_ids);
            })
            ->select(
                'products.id',
                'products.name',
                'line_items.product_id',
                DB::raw('SUM(products.price*line_items.quantity) as total')
            )->groupBy(
                'products.id',
                'line_items.product_id',
                'products.name'
            )->orderBy('total', 'desc')->limit(10)->get();


        $data_refund = [];
        //total refund
        foreach ($topByPriceItemsRefund as $topByPriceItem) {
            $data_refund[$topByPriceItem->id] = $topByPriceItem->total;
        }
        //

        $productsTop = Product::where('store_id', $request->store->id)->whereIn(
            'id',
            $topByPriceItems->pluck("id")
        )->get();

        $dataRTByPrice = [];

        foreach ($topByPriceItems as $topByPriceItem) {

            array_push($dataRTByPrice,  [
                "total_price" => $topByPriceItem->total - ($data_refund[$topByPriceItem->id] ?? 0),
                "product" => $productsTop->where("id", $topByPriceItem->id)->first(),
            ]);
        }

        usort($dataRTByPrice, function ($a, $b) {
            return $a['total_price'] < $b['total_price'] ? 1 : 0;
        });

        ///////////////// topByViewerItems
        $topByViewerItems = DB::table('products')
            ->where('products.store_id', $request->store->id)
            ->leftJoin(
                'viewer_products',
                'viewer_products.product_id',
                '=',
                'products.id'
            )->whereDate('viewer_products.created_at', '>=',  $dateFrom)
            ->whereDate('viewer_products.created_at', '<', $dateTo)
            ->select(
                'products.id',
                'products.name',
                'viewer_products.product_id',
                DB::raw('COUNT(viewer_products.id) as total')
            )->groupBy(
                'products.id',
                'viewer_products.product_id',
                'products.name'
            )->orderBy('total', 'desc')->limit(10)->get();

        $productsTop = Product::where('store_id', $request->store->id)->whereIn(
            'id',
            $topByViewerItems->pluck("id")
        )->get();

        $dataRTByView = [];

        foreach ($topByViewerItems as $topByViewerItem) {
            array_push($dataRTByView,  [
                "view" => $topByViewerItem->total,
                "product" => $productsTop->where("id", $topByViewerItem->id)->first(),
            ]);
        }




        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_items" => $dataRTByTotal,
                "number_of_orders" => $dataRTByNumberOfOrder,
                "total_price" => $dataRTByPrice,
                "view" => $dataRTByView,
            ],
        ], 200);
    }
}
