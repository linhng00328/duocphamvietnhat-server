<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;


/**
 * @group  Customer/Tích điểm
 */
class CustomerRollCallController extends Controller
{
    // /**
    //  * Danh sách điểm danh
    //  * 
    //  * score_in_day điểm sẽ được, danh sách trả về
    //  * 
    //  */
    // public function get_roll_calls(Request $request)
    // {

    //     $dateTimeNow = Helper::getTimeNowDateTime();
    //     $monday = clone $dateTimeNow->modify(('Sunday' == $dateTimeNow->format('l')) ? 'Monday last week' : 'Monday this week');
    //     $sunday = clone $dateTimeNow->modify('Sunday this week');
    //     $dayNow = clone $dateTimeNow;


    //     $carbon = Carbon::now('Asia/Ho_Chi_Minh');
    //     $date1 = $carbon->parse($monday->format('Y-m-d H:i:s'));
    //     $date2 = $carbon->parse($sunday->format('Y-m-d H:i:s'));
    //     $dateNow = $carbon->parse($dayNow->format('Y-m-d H:i:s'));

    //     $days = [];
    //     $scoreInDay = ScoreShared::score1Day();
    //     $scoreInDay7 = ScoreShared::score7Day();
    //     $count = 0;


    //     $lengthOfAd = $dateNow->diffInDays($date1)-1;

     

    //     $j = 2;

    
    //     for ($i =  $date1; $i <=  $date2; $i->addDays(1)) {

    //         $date = $i->format('Y-m-d');
    //         $rollCallExits = RollCall::where("store_id", $request->store->id)
    //             ->where("customer_id", $request->customer->id)
    //             ->where("date_checkin", $date)->first();

    //         if ($rollCallExits != null) {
    //             $count++;
    //         }

    //         $checked = $rollCallExits != null ? true : false;
    //         if($j!=8  && $j < ($lengthOfAd+1)  ) {
            
    //             if( $checked == false) {
                
    //                 $scoreInDay7 = $scoreInDay;
    //             }
    //         }

    //         array_push($days, [
    //             "date" => $i->format('Y-m-d'),
    //             "checked" => $checked,
    //             "score" => $rollCallExits != null ? $rollCallExits->score : 0,
    //             "score_in_day" =>$j!=8 ? $scoreInDay :  $scoreInDay7
    //         ]);
    //         $j++;
    //     }


    //     return response()->json([
    //         'code' => 200,
    //         'success' => true,
    //         'data' =>   [
    //             "list_roll_call" => $days,
    //         ],
    //         'msg_code' => MsgCode::SUCCESS[0],
    //         'msg' => MsgCode::SUCCESS[1],
    //     ], 200);
    // }

    // /**
    //  * Điểm danh
    //  * 
    //  */
    // public function checkin(Request $request)
    // {

    //     $dateTimeNow = Helper::getTimeNowDateTime();
    //     $date_checkin = $dateTimeNow->format('Y-m-d');

    //     $monday = clone $dateTimeNow->modify(('Sunday' == $dateTimeNow->format('l')) ? 'Monday last week' : 'Monday this week');
    //     $sunday = clone $dateTimeNow->modify('Sunday this week');

    //     $carbon = Carbon::now('Asia/Ho_Chi_Minh');
    //     $date1 = $carbon->parse($monday->format('Y-m-d H:i:s'));
    //     $date2 = $carbon->parse($sunday->format('Y-m-d H:i:s'));

    //     $days = [];
    //     $count = 0;
    //     $scoreInDay = ScoreShared::score1Day();

    //     for ($i =  $date1; $i <=  $date2; $i->addDays(1)) {
    //         $date = $i->format('Y-m-d');
    //         $rollCallExits = RollCall::where("store_id", $request->store->id)
    //             ->where("customer_id", $request->customer->id)
    //             ->where("date_checkin", $date)->first();

    //         if ($rollCallExits != null) {
    //             $count++;
    //         }
    //     }

    //     if ($count == 6) {
    //         $scoreInDay = ScoreShared::score7Day();
    //     }

    //     $rollCallExits = RollCall::where("store_id", $request->store->id)
    //         ->where("customer_id", $request->customer->id)
    //         ->where("date_checkin", $date_checkin)->first();

    //     if ($rollCallExits == null) {
    //         RollCall::create([
    //             "store_id" => $request->store->id,
    //             "customer_id" => $request->customer->id,
    //             "score" =>  $scoreInDay,
    //             "date_checkin" =>  $date_checkin
    //         ]);

    //         PointCustomerUtils::add_sub_point(
    //             ($count == 6) ? PointCustomerUtils::ROLL_CALL_7_DAY:PointCustomerUtils::ROLL_CALL_1_DAY,
    //             $request->store->id,
    //             $request->customer->id,
    //             $scoreInDay
    //         );
    //     } else {

    //         return response()->json([
    //             'code' => 400,
    //             'success' => false,
    //             'msg_code' => MsgCode::CHECKED_IN_TODAY[0],
    //             'msg' => MsgCode::CHECKED_IN_TODAY[1],
    //         ], 400);
    //     }

    //     return response()->json([
    //         'code' => 200,
    //         'success' => true,
    //         'msg_code' => MsgCode::SUCCESS[0],
    //         'msg' => MsgCode::SUCCESS[1],
    //     ], 200);
    // }
}
