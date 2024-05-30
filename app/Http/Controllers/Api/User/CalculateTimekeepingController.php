<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CheckinCheckoutUtils;
use App\Helper\Helper;
use App\Helper\ShiftUtils;
use App\Http\Controllers\Controller;
use App\Models\CalendarShift;
use App\Models\DateTimekeeping;
use App\Models\DateTimekeepingHistory;
use App\Models\DateTimekeepingShift;
use App\Models\MsgCode;
use App\Models\ProductShifts;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffInCalendar;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;

/**
 * @group  User/Tính công làm việc
 */

class CalculateTimekeepingController extends Controller
{

    function groupHistory($keepingHistories)
    {

        $arr = [];
        foreach ($keepingHistories as $his1) {

            $h1 = null;
            $h2 = null;

            if ($his1->is_checkin  == true) {
                foreach ($keepingHistories as $his2) {

                    if ($his2->checkout_for_checkin_id == $his1->id) {
                        $h1 = $his1;
                        $h2 = $his2;
                    }
                }
            }

            if ($h1 != null && $h2 != null) {
                array_push($arr, $h1);
                array_push($arr, $h2);
            }
        }

        return  $arr;
    }
    /**
     * 
     * Tính số giờ làm việc
     * 
     * Nếu ngày kết thúc - ngày bắt đầu lớn hơn 1 thì  sẽ không có keeping_histories (danh sách checkin checkout)
     * 
     * @queryParam date_from string datetime Ngày bắt đầu
     * @queryParam date_to string datetime Ngày kết thúc
     * 
     */
    public function getTimeKeeping(Request $request, $id)
    {

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';



        $is_one_day = false;

        $date2Check = clone $date2;
        if ($date2Check->subDays(1) <= $date1) {
            $is_one_day = true;
        }

        $list = [];


        $list_staff_timekeeping = [];

        for ($i = $date1; $i <= $date2; $i->addDays(1)) {
            $date = $i->year . '-' . $i->month . '-' . $i->day;
            $date = $carbon->parse($date);
            $date = $date->format('Y-m-d');

            $dateTimekeepings  = DateTimekeeping::where('store_id', $request->store->id)
                ->where('branch_id', $request->branch->id)
                ->where('date',  $date)
                ->get();



            foreach ($dateTimekeepings  as   $dateTimekeeping) {


                $keepingHistories = DateTimekeepingHistory::where('store_id', $request->store->id)
                    ->where('date',  $date)
                    ->where('date_timekeeping_id',   $dateTimekeeping->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('staff_id', $dateTimekeeping->staff_id)
                    ->orderBy('created_at', 'asc')
                    ->get();



                $keepingHistories  =   $this->groupHistory($keepingHistories);

                $shifs = DateTimekeepingShift::where('store_id', $request->store->id)
                    ->where('date',  $date)
                    ->where('date_timekeeping_id',   $dateTimekeeping->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('staff_id', $dateTimekeeping->staff_id)
                    ->orderBy('start_work_hour', 'asc')
                    ->get();


                $data_total_seconds_recording_time = $this->total_seconds($shifs,  $keepingHistories);
                $data_total_seconds_break_time = $this->total_seconds_breack_time($shifs,  $keepingHistories);

                $recording_time = $data_total_seconds_recording_time["recording_time"];
                $total_seconds = $data_total_seconds_recording_time["total_seconds"] ;


                $total_seconds_break_time = $data_total_seconds_break_time["total_seconds"] ?? 0;

                $total_seconds =   $total_seconds  -  $total_seconds_break_time;

                $total_salary =  ($total_seconds / 60 / 60) * $dateTimekeeping->salary_one_hour;


                if (isset($list_staff_timekeeping[$dateTimekeeping->staff_id])) {

                    $list_staff_timekeeping[$dateTimekeeping->staff_id] = [
                        'staff' => Staff::select('id', 'name', 'phone_number')->where('id', $dateTimekeeping->staff_id)->first(),
                        "total_seconds" =>  $list_staff_timekeeping[$dateTimekeeping->staff_id]['total_seconds'] + $total_seconds,
                        "total_salary" =>   $list_staff_timekeeping[$dateTimekeeping->staff_id]['total_salary'] + $total_salary,
                        "recording_time" => $recording_time,
                        "salary_one_hour" =>  $is_one_day  == true ?  $dateTimekeeping->salary_one_hour : null,
                        "keeping_histories" =>    $is_one_day  == true ?  $keepingHistories  : [],
                        "shift_work" =>  $is_one_day  == true ? DateTimekeepingShift::where('store_id', $request->store->id)
                            ->where('branch_id', $request->branch->id)
                            ->where('staff_id', $dateTimekeeping->staff_id)
                            ->where('date', $date)
                            ->get() : array()
                    ];
                } else {

                    $list_staff_timekeeping[$dateTimekeeping->staff_id] = [
                        'staff' => Staff::select('id', 'name', 'phone_number')->where('id', $dateTimekeeping->staff_id)->first(),
                        "total_seconds" =>  $total_seconds,
                        "total_salary" =>  $total_salary,
                        "recording_time" => $recording_time,
                        "salary_one_hour" => $dateTimekeeping->salary_one_hour,
                        "keeping_histories" =>    $is_one_day  == true ?  $keepingHistories  : [],
                        "shift_work" =>  $is_one_day  == true ? DateTimekeepingShift::where('store_id', $request->store->id)
                            ->where('branch_id', $request->branch->id)
                            ->where('staff_id', $dateTimekeeping->staff_id)
                            ->where('date', $date)
                            ->get() : array()
                    ];
                }
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "date_from" =>   $carbon->parse($dateFrom),
                "date_to" =>  $carbon->parse($dateTo),
                "list_staff_timekeeping" =>  array_values($list_staff_timekeeping)
            ]
        ], 200);
    }



    function total_seconds_breack_time($shifs, $histories)
    {


        $list_shift = [];
        foreach ($shifs as $shift) {
            array_push($list_shift, [
                'start_work_hour' =>  $shift->start_break_hour ?? "00",
                'start_work_minute' => $shift->start_break_minute ?? "00",
                'end_work_hour' => $shift->end_break_hour ?? "00",
                'end_work_minute' => $shift->end_break_minute ?? "00", 
            ]);
        }

        if(empty($shift->start_break_hour) || empty( $shift->end_break_hour )) {
            return  [
                "recording_time" => 0,
                "total_seconds" => 0
            ];
        }



        $list_history = [];
        $last_checkin = [];


        foreach ($histories as $history) {


            if ($history->is_checkin == true) {
                $last_checkin['time_check'] =  $history->time_check;
                $last_checkin['id'] = $history->id;

                $history_check_in  = $history;
                $history_check_out = DateTimekeepingHistory::where('checkout_for_checkin_id',   $history_check_in->id)
                    ->first();

                if (
                    $history_check_out != null && ($history_check_in->status == CheckinCheckoutUtils::STATUS_CHECKED
                        || $history_check_in->status == CheckinCheckoutUtils::STATUS_OK_CHECKIN)
                    && ($history_check_out->status == CheckinCheckoutUtils::STATUS_CHECKED
                        || $history_check_out->status == CheckinCheckoutUtils::STATUS_OK_CHECKIN)
                ) {

                    array_push($list_history, [
                        'time_check_in' =>    $history_check_in->time_check,
                        'time_check_out' => $history_check_out->time_check,
                        'total_in_time' => 0,
                        'is_bonus' =>  $history_check_in->is_bonus,
                        'from_user' => $history_check_in->from_user,

                    ]);
                }
            }
        }
        //  dd($list_history);

        $total_seconds = 0;
        $i = 1;


        $recording_time = [];


        foreach ($list_history as $time_checkin_and_checkout) {

            $k = $time_checkin_and_checkout;
            $total_in_time = $this->one_check_in_all_shift($time_checkin_and_checkout,  $list_shift, $i);
            $total_in_time = $k['is_bonus'] == true ? $total_in_time : -$total_in_time;

            
            $total_seconds += $total_in_time;

            $k['total_in_time'] = $total_in_time;

            $i++;

            array_push($recording_time, $k);
        }


        return  [
            "recording_time" => $recording_time,
            "total_seconds" => $total_seconds
        ];
    }

    function total_seconds($shifs, $histories)
    {


        $list_shift = [];
        foreach ($shifs as $shift) {
            array_push($list_shift, [
                'start_work_hour' =>  $shift->start_work_hour ?? "00",
                'start_work_minute' => $shift->start_work_minute ?? "00",
                'end_work_hour' => $shift->end_work_hour ?? "00",
                'end_work_minute' => $shift->end_work_minute ?? "00",
            ]);
        }

        $list_history = [];
        $last_checkin = [];


        foreach ($histories as $history) {


            if ($history->is_checkin == true) {
                $last_checkin['time_check'] =  $history->time_check;
                $last_checkin['id'] = $history->id;

                $history_check_in  = $history;
                $history_check_out = DateTimekeepingHistory::where('checkout_for_checkin_id',   $history_check_in->id)
                    ->first();

                if (
                    $history_check_out != null && ($history_check_in->status == CheckinCheckoutUtils::STATUS_CHECKED
                        || $history_check_in->status == CheckinCheckoutUtils::STATUS_OK_CHECKIN)
                    && ($history_check_out->status == CheckinCheckoutUtils::STATUS_CHECKED
                        || $history_check_out->status == CheckinCheckoutUtils::STATUS_OK_CHECKIN)
                ) {

                    array_push($list_history, [
                        'time_check_in' =>    $history_check_in->time_check,
                        'time_check_out' => $history_check_out->time_check,
                        'total_in_time' => 0,
                        'is_bonus' =>  $history_check_in->is_bonus,
                        'from_user' => $history_check_in->from_user,

                    ]);
                }
            }
        }
        //  dd($list_history);

        $total_seconds = 0;
        $i = 1;


        $recording_time = [];


        foreach ($list_history as $time_checkin_and_checkout) {

            $k = $time_checkin_and_checkout;
            $total_in_time = $this->one_check_in_all_shift($time_checkin_and_checkout,  $list_shift, $i);
            $total_in_time = $k['is_bonus'] == true ? $total_in_time : -$total_in_time;


            $total_seconds += $total_in_time;

            $k['total_in_time'] = $total_in_time;

            $i++;

            array_push($recording_time, $k);
        }


        return  [
            "recording_time" => $recording_time,
            "total_seconds" => $total_seconds
        ];
    }

    function one_check_in_all_shift($time_checkin_and_checkout,  $list_shift, $i)
    {
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $from_user = $time_checkin_and_checkout['from_user'];

        if ($from_user  == true) {
            $time_check_in = $carbon->parse($time_checkin_and_checkout['time_check_in']);
            $time_check_out = $carbon->parse($time_checkin_and_checkout['time_check_out']);

            $seconds = $time_check_in->diffInSeconds($time_check_out);
            return $seconds;
        }


        $total = 0;

        foreach ($list_shift as $shift_time) {


            try {
                $shift_time1 = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' ' . $shift_time['start_work_hour'] . ':' . $shift_time['start_work_minute'] . ':00';
                $shift_time2 = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' ' . $shift_time['end_work_hour'] . ':' . $shift_time['end_work_minute'] . ':00';
    
                $shift_time1 = $carbon->parse($shift_time1);
                $shift_time2 = $carbon->parse($shift_time2);
    
    
                $time_check_in = $carbon->parse($time_checkin_and_checkout['time_check_in']);
                $time_check_out = $carbon->parse($time_checkin_and_checkout['time_check_out']);
                
                $time_check_in = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' ' . $time_check_in->hour . ':' . $time_check_in->minute . ':' . $time_check_in->second;
                $time_check_out = $carbon->year . '-' . $carbon->month . '-' . $carbon->day . ' ' . $time_check_out->hour . ':' . $time_check_out->minute . ':' . $time_check_out->second;
    
    
                $time_check_in = $carbon->parse($time_check_in);
                $time_check_out = $carbon->parse($time_check_out);
    
    
                if (
                    $time_check_in <= $shift_time1 && $time_check_out <= $shift_time1
                    ||
                    $time_check_in >= $shift_time2 && $time_check_out >= $shift_time2
                ) {
                } else {
                    if ($time_check_in < $shift_time1) {
                        $time_check_in  = $shift_time1;
                    }
    
                    if ($time_check_out > $shift_time2) {
                        $time_check_out  = $shift_time2;
                    }
    
                    $seconds = $time_check_out->diffInSeconds($time_check_in);
                    $total += $seconds;
                }
            } catch (Exception $c){

            }
           
        }

        if ($i == 1) {
            //dd($total, $time_checkin_and_checkout);
        }

        return   $total;
    }
}
