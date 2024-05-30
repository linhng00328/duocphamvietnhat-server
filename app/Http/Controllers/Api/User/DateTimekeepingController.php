<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\CheckinLocation;
use App\Models\DateTimekeeping;
use App\Models\DateTimekeepingHistory;
use App\Models\DateTimekeepingShift;
use App\Models\MobileCheckin;
use App\Models\MsgCode;
use App\Models\Shift;
use App\Models\StaffInCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group  User/Lịch làm việc
 */

class DateTimekeepingController extends Controller
{

    //Them shift vao lich  DateTimekeeping là tổng lưu staff với date
    public function add_shift_to_datekeeping($request)
    {
        $date = Carbon::now('Asia/Ho_Chi_Minh');

        $dateFrom = $date->year . '-' . $date->month . '-' . $date->day . ' 00:00:00';
        $dateTo = $date->year . '-' . $date->month . '-' . $date->day . ' 23:59:59';
        $date = $date->year . '-' . $date->month . '-' . $date->day;

        $staff =  $request->staff;

        // $staff = Staff
        $staffInCalendars = StaffInCalendar::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('start_time', '<=',  $dateFrom)
            ->where('end_time', '>=', $dateTo)
            ->where('staff_id',  $staff->id)
            ->orderBy('id', 'asc')
            ->get();


        $dayofweek = date('w', strtotime($dateFrom));
        $dayofweek = Helper::day_php_to_standard($dayofweek);


        $staff_work = [];

        //shift đã phân công ngày này
        $arr_shift = [];



        //Danh sach staff trong calendar
        foreach ($staffInCalendars as $staffInCalendar) {

            $shift = Shift::where('store_id', $request->store->id)
                ->where('id',   $staffInCalendar->shift_id)->first();

            $arr_shift[$shift->id] =  $shift;
            if ($shift  != null) {
                //Them staff nếu is add = true
                if (
                    $staff != null && $staffInCalendar->is_add == true &&
                    ((in_array($dayofweek, $shift->days_of_week_list) && $shift->is_put_a_lot == true)
                        ||
                        $shift->is_put_a_lot == false
                    )
                ) {

                    $arr_shift[$shift->id] =  $shift;
                }
                //xoa staff nếu is add = false
                if ($staff != null && $staffInCalendar->is_add == false) {

                    unset($arr_shift[$shift->id]);
                }
            }
        }




        $dateTimekeeping  = DateTimekeeping::where('store_id', $request->store->id)
            ->where('branch_id',  $request->branch->id)
            ->where('staff_id', $request->staff->id)
            ->where('date', $date)->first();


        //Nếu chưa có data tổng
        if ($dateTimekeeping == null) {
            $dateTimekeeping  = DateTimekeeping::create([
                "store_id" =>  $request->store->id,
                "branch_id" =>  $request->branch->id,
                "staff_id" => $request->staff->id,
                "salary_one_hour" => $request->staff->salary_one_hour,
                "date"  => $date,
            ]);

            foreach ($arr_shift  as $shift) {
                DateTimekeepingShift::create([

                    "store_id" =>  $request->store->id,
                    "branch_id" =>  $request->branch->id,
                    "staff_id" => $request->staff->id,

                    "date_timekeeping_id" =>   $dateTimekeeping->id,

                    "shift_id" =>  $shift->id,

                    "name" =>  $shift->name,
                    "code" =>  $shift->code,

                    "start_work_hour" =>  $shift->start_work_hour,
                    "start_work_minute" =>  $shift->start_work_minute,
                    "end_work_hour" =>  $shift->end_work_hour,
                    "end_work_minute" =>  $shift->end_work_minute,

                    "start_break_hour" =>  $shift->start_break_hour,
                    "start_break_minute" =>  $shift->start_break_minute,
                    "end_break_hour" =>  $shift->end_break_hour,
                    "end_break_minute" =>  $shift->end_break_minute,

                    "minutes_late_allow" =>  $shift->minutes_late_allow,
                    "minutes_early_leave_allow" =>  $shift->minutes_early_leave_allow,
                    "days_of_week" =>  $shift->days_of_week,

                    "updated_time_last" =>  $shift->updated_at,

                    "date"  => $date,
                ]);
            }
        } else {

            $dateTimekeeping->update([
                "store_id" =>  $request->store->id,
                "branch_id" =>  $request->branch->id,
                "staff_id" => $request->staff->id,
                "salary_one_hour" => $request->staff->salary_one_hour,
                "date"  => $date,
            ]);

            $has_change = false;


            foreach ($arr_shift  as $shift) {

                $shift_his = DateTimekeepingShift::where('store_id', $request->store->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('date', $date)
                    ->where('staff_id',  $staff->id)
                    ->where('updated_time_last',  $shift->updated_at)
                    ->where('shift_id',  $shift->id)->first();

                if ($shift_his == null) {
                    $has_change = true;
                }
            }
            $shift_his_all = DateTimekeepingShift::where('store_id', $request->store->id)
                ->where('branch_id', $request->branch->id)
                ->where('staff_id',  $staff->id)
                ->where('date', $date)
                ->get();

            if (count($shift_his_all) != count($arr_shift)) {
                $has_change = true;
            }


            if ($has_change == true) {
                DateTimekeepingShift::where('store_id', $request->store->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('staff_id',  $staff->id)
                    ->where('date', $date)->delete();


                //Lấy tất cả shift tạo công việc để show ra
                foreach ($arr_shift  as $shift) {
                    DateTimekeepingShift::create([

                        "store_id" =>  $request->store->id,
                        "branch_id" =>  $request->branch->id,
                        "staff_id" => $request->staff->id,

                        "date_timekeeping_id" =>   $dateTimekeeping->id,

                        "shift_id" =>  $shift->id,

                        "name" =>  $shift->name,
                        "code" =>  $shift->code,

                        "start_work_hour" =>  $shift->start_work_hour,
                        "start_work_minute" =>  $shift->start_work_minute,
                        "end_work_hour" =>  $shift->end_work_hour,
                        "end_work_minute" =>  $shift->end_work_minute,

                        "start_break_hour" =>  $shift->start_break_hour,
                        "start_break_minute" =>  $shift->start_break_minute,
                        "end_break_hour" =>  $shift->end_break_hour,
                        "end_break_minute" =>  $shift->end_break_minute,

                        "minutes_late_allow" =>  $shift->minutes_late_allow,
                        "minutes_early_leave_allow" =>  $shift->minutes_early_leave_allow,
                        "days_of_week" =>  $shift->days_of_week,

                        "updated_time_last" =>  $shift->updated_at,

                        "date"  => $date,
                    ]);
                }
            }
        }
    }

    /**
     * 
     * Thông tin ca hôm nay
     * 
     */
    public function get_to_day(Request $request, $id)
    {


        $date = Carbon::now('Asia/Ho_Chi_Minh');
        $date = $date->year . '-' . $date->month . '-' . $date->day;
        $this->add_shift_to_datekeeping($request);


        $shift_working = DateTimekeepingShift::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('date', $date)
            ->orderBy('start_work_hour', "ASC")
            ->where('staff_id', $request->staff->id)
            ->get();


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  [
                'shift_working' => $shift_working,
                'history_checkin_checkout' =>
                DateTimekeepingHistory::where('store_id', $request->store->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('staff_id', $request->staff->id)
                    ->orderBy('id', 'desc')
                    ->where('date', $date)->get()
            ],
        ], 200);
    }


    /**
     * 
     * Checkin checkout
     * 
     * 0 ok, 1 chờ duyet, 2 da duyet, 3 huy
     * 
     * @bodyParam is_remote boolean Có phải chấm công từ xa không
     * @bodyParam wifi_name string wifi_name
     * @bodyParam wifi_mac string wifi_mac (check)
     * @bodyParam device_name string tên máy
     * @bodyParam device_id string device id (check)
     * @bodyParam reason string lý do (trường hợp check từ xa)
     * 
     */
    public function checkin_checkout(Request $request, $id)
    {

        $date = Carbon::now('Asia/Ho_Chi_Minh');
        $date =  $date->year . '-' . $date->month . '-' . $date->day;


        $this->add_shift_to_datekeeping($request);

        $shift_working = DateTimekeepingShift::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('staff_id', $request->staff->id)
            ->orderBy('start_work_hour', "ASC")
            ->where('date', $date)
            ->get();
        if (count($shift_working) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIFT_TODAY[0],
                'msg' => MsgCode::NO_SHIFT_TODAY[1],
            ], 400);
        }


        //Check vị trí
        if ($request->is_remote == false) {
            $checkinLocation = CheckinLocation::where('store_id', $request->store->id)
                ->where('wifi_mac', $request->wifi_mac)
                ->where('branch_id', $request->branch->id)
                ->first();
            if ($checkinLocation == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LOCATION_CHECKIN[0],
                    'msg' => MsgCode::INVALID_LOCATION_CHECKIN[1],
                ], 400);
            }
        }

        $checkinMobile = MobileCheckin::where('store_id', $request->store->id)
            ->where('device_id', $request->device_id)
            ->where('staff_id', $request->staff->id)
            ->where('branch_id', $request->branch->id)
            ->where('status', 1)
            ->first();

        if ($checkinMobile == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_MOBILE_CHECKIN[0],
                'msg' => MsgCode::INVALID_MOBILE_CHECKIN[1],
            ], 400);
        }

        if ($request->is_remote == true) {
            if (empty($request->reason)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::REASON_IS_REQUIRED[0],
                    'msg' => MsgCode::REASON_IS_REQUIRED[1],
                ], 400);
            }
        }


        $last_checkin_checkout =
            DateTimekeepingHistory::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->orderBy('id', 'desc')
            ->where('staff_id', $request->staff->id)
            ->where('date', $date)->first();

        $is_checkin = false;
        $checkout_for_checkin_id = null;

        if ($last_checkin_checkout  == null ||   $last_checkin_checkout->is_checkin == false) {
            $is_checkin = true;
        } else {
            $checkout_for_checkin_id =   $last_checkin_checkout->id;
        }

        $dateTimekeeping  = DateTimekeeping::where('store_id', $request->store->id)

            ->where('branch_id',  $request->branch->id)
            ->where('staff_id', $request->staff->id)
            ->where('date', $date)->first();


        if ($dateTimekeeping == null) {
            $dateTimekeeping  = DateTimekeeping::create([
                "store_id" =>  $request->store->id,
                "branch_id" =>  $request->branch->id,
                "staff_id" => $request->staff->id,
                "salary_one_hour" => $request->staff->salary_one_hour,
                "date"  => $date,
            ]);
        } else {
            $dateTimekeeping->update([
                "store_id" =>  $request->store->id,
                "branch_id" =>  $request->branch->id,
                "staff_id" => $request->staff->id,
                "salary_one_hour" => $request->staff->salary_one_hour,
                "date"  => $date,
            ]);
        }

        DateTimekeepingHistory::create([
            "store_id" =>  $request->store->id,
            "branch_id" =>  $request->branch->id,
            "staff_id" => $request->staff->id,
            "date_timekeeping_id" => $dateTimekeeping->id,
            "time_check" => Helper::getTimeNowString(),
            "is_checkin" => $is_checkin,
            "status" => $request->is_remote == false ? 0 : 1,
            "note" => $request->note,
            "checkout_for_checkin_id" =>   $checkout_for_checkin_id,
            "remote_timekeeping" => filter_var($request->is_remote, FILTER_VALIDATE_BOOLEAN),
            "reason" => $request->reason,
            "wifi_name" => $request->wifi_name,
            "wifi_mac" => $request->wifi_mac,
            "date"  => $date,
            'from_staff_id' => $request->staff == null ? null :  $request->staff->id,
            'from_user_id' => $request->user == null ? null :  $request->user->id,
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  [
                'shift_working' => $shift_working,
                'history_checkin_checkout' =>  DateTimekeepingHistory::where('store_id', $request->store->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('staff_id', $request->staff->id)
                    ->orderBy('id', 'desc')
                    ->where('date', $date)->get()
            ],
        ], 200);
    }
}
