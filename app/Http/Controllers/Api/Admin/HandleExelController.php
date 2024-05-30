<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Place;
use App\Helper\PointCustomerUtils;
use App\Helper\StatusDefineCode;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\ConfigDataExample;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\PointHistory;
use App\Models\PointSetting;
use App\Models\Store;
use App\Services\Shipper\GHN\GHNUtils;
use DateTime;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @group  Admin/Xử lý exel của khách hàng
 */

class HandleExelController extends Controller
{
    /**
     * Cấu hình data ví dụ
     * 
     * 
     * 
     */
    public function handleExel(Request $request)
    {
        // $arr = [];
        // $pointSetting = PointSetting::where(
        //     'store_id',
        //     2030
        // )->first();


        // $customers = Customer::where('store_id', 2030)->get();
        // foreach ($customers as $c) {
        //     $deletes = PointHistory::where(
        //         'store_id',
        //         2030
        //     )->where(
        //         'customer_id',
        //         $c->id
        //     )
        //         ->delete();

        //     $c->update(['points' =>  0]);

        //     $total_after_discount = $c->total_after_discount;
        //     $total_after_discount = 0;
        //     $total_referrals = $c->total_referrals;
        //     $total_score_register  = 0;
        //     $total_score = 0;

        //     if ($c->official == true) {
        //         $total_score =  $total_score + 1;
        //         PointCustomerUtils::add_sub_point(
        //             PointCustomerUtils::REGISTER_CUSTOMER,
        //             2030,
        //             $c->id,
        //             1,
        //             $c->id,
        //             $c->phone_number
        //         );
        //     }

        //     //tính điểm cho customer
        //     if ($pointSetting != null) {
        //         $orders =  DB::table('orders')->where('order_status', StatusDefineCode::COMPLETED)->where('store_id', 2030)->where('phone_number', $c->phone_number)->get();

        //         foreach ($orders as $order) {
        //             $total_after_discount = $order->total_after_discount;
        //             //Thêm đỉm thưởng xu
        //             if ($pointSetting->money_a_point  > 0 && $pointSetting->percent_refund > 0 && $pointSetting->percent_refund <= 100) {
        //                 $moneyRefund = $total_after_discount * ($pointSetting->percent_refund / 100);
        //                 $point = (int)($moneyRefund / $pointSetting->money_a_point);
        //                 //Kiem tra so luong xu toi da duoc tang khi mua hang
        //                 $point =   $point;
        //                 $total_score =  $total_score + $point;
        //                 if ($point > 0) {
        //                     PointCustomerUtils::add_sub_point(
        //                         PointCustomerUtils::ORDER_COMPLETE,
        //                         2030,
        //                         $c->id,
        //                         (int)($point),
        //                         $order->id,
        //                         $order->order_code
        //                     );
        //                 }
        //             }
        //         }
        //     }
        // }

        // $customers = Customer::where('store_id', 2030)->get();
        // foreach ($customers as $c) {

        //     //Customer refe
        //     $customerRefs = DB::table('customers')->where('store_id', 2030)->where('referral_phone_number', $c->phone_number)->get();
        //     foreach ($customerRefs as $cRef) {

        //         PointCustomerUtils::add_sub_point(
        //             PointCustomerUtils::REFERRAL_CUSTOMER,
        //             2030,
        //             $c->id,
        //             1,
        //             $cRef->id,
        //             $cRef->phone_number
        //         );
        //        // $total_score++;
        //     }
        // }

        // array_push($arr, [
        //     'name' => $c->name,
        //     'phone_number' => $c->phone_number,
        //     'total_score' => $total_score
        // ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        //    'data' => $arr
        ], 200);
    }
}
