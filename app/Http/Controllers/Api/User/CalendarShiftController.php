<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\ShiftUtils;
use App\Http\Controllers\Controller;
use App\Models\CalendarShift;
use App\Models\MsgCode;
use App\Models\ProductShifts;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffInCalendar;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

/**
 * @group  User/Lịch làm việc
 */

class CalendarShiftController extends Controller
{
    /**
     * 
     * Danh sách lich làm việc
     * 
     */
    public function getAll(Request $request, $id)
    {

        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        if ($date2->month - $date1->month > 2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::GREAT_TIME[0],
                'msg' => MsgCode::GREAT_TIME[1],
            ], 400);
        }

        $arr_staff = [];

        $staffs = Staff::where('store_id', $request->store->id)->get();
        foreach ($staffs as $staff) {
            $arr_staff[$staff->id] = $staff;
        }



        $shifts = Shift::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->orderBy('start_work_hour', 'asc')
            ->get();


        $data_res = [];

        foreach ($shifts  as  $shift) {



            $has_staff = false;
            $staff_in_time = [];

            $date1Clone = clone $date1;
            $date2Clone = clone $date2;


            for ($i = $date1Clone; $i <= $date2Clone; $i->addDays(1)) {

                $date = $i->format('Y-m-d');

                $dayofweek = date('w', strtotime($date));
                $dayofweek = Helper::day_php_to_standard($dayofweek);


                $start = $carbon->parse($date);
                $end = $carbon->parse($date);

                $start = $start->year . '-' . $start->month . '-' . $start->day . ' 00:00:00';
                $end = $end->year . '-' . $end->month . '-' . $end->day . ' 23:59:59';

                $staffInCalendars = StaffInCalendar::where('store_id', $request->store->id)
                    ->where('branch_id', $request->branch->id)
                    ->where('start_time', '<=',  $start)
                    ->where('shift_id',  $shift->id)
                    ->where('end_time', '>=', $end)
                    ->orderBy('id', 'asc')
                    ->get();
                $staff_work = [];


                //Danh sach staff trong calendar
                foreach ($staffInCalendars as $staffInCalendar) {

                    $staff = $arr_staff[$staffInCalendar->staff_id];



                    //Them staff nếu is add = true
                    if (
                        $staff != null && $staffInCalendar->is_add == true &&
                        ((in_array($dayofweek, $shift->days_of_week_list) && $staffInCalendar->is_put_a_lot == true)
                            ||
                            $staffInCalendar->is_put_a_lot == false
                        )
                    ) {


                        $data_staff = [
                            'name' => $staff->name,
                            'id' => $staff->id,
                            'avatar_image' => $staff->avatar_image
                        ];

                        $staff_work[$staff->id] = $data_staff;
                    }
                    //xoa staff nếu is add = false
                    if ($staff != null && $staffInCalendar->is_add == false) {

                        if (isset($staff_work[$staff->id])) {
                            unset($staff_work[$staff->id]);
                        }
                    }
                }

                //lay value mang staff
                $staff_work = array_values($staff_work);


                if (count($staff_work) > 0) {
                    $has_staff = true;
                }


                //them vao danh sach  gio
                array_push(
                    $staff_in_time,
                    [
                        'date' => $i->format('Y-m-d'),
                        'staff_work' => $staff_work
                    ]
                );
            }

            if ($has_staff == true) {
                array_push(
                    $data_res,
                    [
                        'shift' => $shift,
                        'has_staff' =>  $has_staff,
                        'staff_in_time' =>  $staff_in_time
                    ]
                );
            }
        }


        // usort($data_res, 'static::shiftSort');

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $data_res,
        ], 200);
    }


    static public function shiftSort($item1, $item2)
    {
        if ($item1->start_work_hour == $item2->start_work_hour) return 0;
        return ($item1->start_work_hour < $item2->start_work_hour) ? 1 : -1;
    }
    /**
     * Xếp ca hàng loạt
     * @urlParam  store_code required Store code cần update
     * @urlParam  branch_id required branch_id Chi nhánh
     * @bodyParam list_shift_id List Danh sách id ca 
     * @bodyParam list_staff_id List Danh sách nhân viên
     * @bodyParam start_time datetime required Thời gian bắt đầu
     * @bodyParam end_time datetime required Thời gian kết thúc
     *
     */
    public function put_a_lot(Request $request)
    {

        if ($request->start_time == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::START_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::START_TIME_IS_REQUIRED[1],
            ], 400);
        }

        if ($request->end_time == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 400);
        }

        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $now =  $carbon->parse(Helper::getTimeNowString());
        $now = $now->year . '-' . $now->month . '-' . $now->day . ' 00:00:00';
        $now = new DateTime($now);

        $d1 = new DateTime($request->start_time);
        $d2 = new DateTime($request->end_time);

        if ($d1 < $now) {
            $d1 = $now;
        }

        if ($d1 > $d2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_GREATE_START_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_GREATE_START_TIME_IS_REQUIRED[1],
            ], 400);
        }


        if ($now > $d2) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }


        if ($request->list_staff_id == null || !is_array($request->list_staff_id) || count($request->list_staff_id) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 400);
        }

        //Check staff
        foreach ($request->list_staff_id  as $staff_id) {
            $staffExist = Staff::where('id', $staff_id)->where('store_id', $request->store->id)
                ->first();

            if ($staffExist == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                    'msg' => MsgCode::NO_STAFF_EXISTS[1],
                ], 400);
            }
        }

        //Check ca
        if ($request->list_shift_id == null || !is_array($request->list_shift_id) || count($request->list_shift_id) == 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIFT_EXISTS[0],
                'msg' => MsgCode::NO_SHIFT_EXISTS[1],
            ], 400);
        }

        $arr_shift = [];
        foreach ($request->list_shift_id  as $shift_id) {
            $shiftExist = Shift::where('id', $shift_id)->where('store_id', $request->store->id)
                ->first();

            if ($shiftExist == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SHIFT_EXISTS[0],
                    'msg' => MsgCode::NO_SHIFT_EXISTS[1],
                ], 400);
            }
            array_push($arr_shift, $shiftExist);
        }

        if (count($arr_shift) > 1) {
            foreach ($arr_shift as $shift) {
                foreach ($arr_shift as $shift2) {
                    if ($shift->id != $shift2->id) {
                        if (ShiftUtils::check_duplicate($shift, $shift2)) {

                            return response()->json([
                                'code' => 400,
                                'success' => false,
                                'msg_code' => MsgCode::DUPLICATE_SHIFT[0],
                                'msg' => MsgCode::DUPLICATE_SHIFT[1],
                            ], 400);
                        }
                    }
                }
            }
        }

        $d1 =  $carbon->parse($d1);
        $d2 =  $carbon->parse($d2);
        $d1 = $d1->year . '-' . $d1->month . '-' . $d1->day . ' 00:00:00';
        $d2 = $d2->year . '-' . $d2->month . '-' . $d2->day . ' 23:59:59';



        $staff_ids = StaffInCalendar::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('start_time', '<=',  $d1)
            ->where('end_time', '>=', $d2)
            ->orderBy('id', 'asc')
            ->pluck('staff_id')->toArray();

        $staff_ids = array_unique($staff_ids);


        foreach ($arr_shift as $shift) {
            $calendar  = CalendarShift::create([
                'store_id' => $request->store->id,
                'branch_id' => $request->branch->id,
                'shift_id' => $shift->id,
                'is_add' => false,
                "start_time" =>  $d1,
                "end_time" => $d2
            ]);
            foreach ($staff_ids  as $staff_id) {
                StaffInCalendar::create([
                    'store_id' => $request->store->id,
                    'branch_id' => $request->branch->id,
                    'shift_id' => $shift->id,
                    'calendar_shift_id' =>  $calendar->id,
                    "staff_id" => $staff_id,
                    'is_add' => false,
                    "start_time" =>  $d1,
                    "end_time" => $d2
                ]);
            }
        }

        foreach ($arr_shift as $shift) {
            $calendar  = CalendarShift::create([
                'store_id' => $request->store->id,
                'branch_id' => $request->branch->id,
                'shift_id' => $shift->id,
                'is_add' => true,
                "start_time" =>  $d1,
                "end_time" => $d2,
                "is_put_a_lot" => true
            ]);
            foreach ($request->list_staff_id  as $staff_id) {
                StaffInCalendar::create([
                    'store_id' => $request->store->id,
                    'branch_id' => $request->branch->id,
                    'shift_id' => $shift->id,
                    'calendar_shift_id' =>  $calendar->id,
                    "staff_id" => $staff_id,
                    'is_add' => true,
                    "start_time" =>  $d1,
                    "end_time" => $d2,
                    "is_put_a_lot" => true
                ]);
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            // 'data' => "OK",
        ], 200);
    }

    /**
     * Xếp ca vào 1 ô (ngày và giờ cụ thể)
     * @urlParam  store_code required Store code cần update
     * @urlParam  branch_id required branch_id Chi nhánh
     * @bodyParam shift_id int Id ca 
     * @bodyParam list_staff_ids List Danh sách nhân viên
     * @bodyParam date datetime required Ngày
     * @body
     *
     */
    public function put_one(Request $request)
    {

        if ($request->date == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }


        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date = new DateTime($request->date);
        $now =  $carbon->parse(Helper::getTimeNowString());
        $now = $now->year . '-' . $now->month . '-' . $now->day . ' 00:00:00';
        $now = new DateTime($now);

        if ($now > $date) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }


        if ($request->list_staff_ids == null || !is_array($request->list_staff_ids)) {
            $request->list_staff_ids = [];
        }

        //Check staff
        foreach ($request->list_staff_ids  as $staff_id) {
            $staffExist = Staff::where('id', $staff_id)->where('store_id', $request->store->id)
                ->first();

            if ($staffExist == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                    'msg' => MsgCode::NO_STAFF_EXISTS[1],
                ], 400);
            }
        }

        //Check ca
        $shiftExist = Shift::where('id', $request->shift_id)->where('store_id', $request->store->id)
            ->first();

        if ($shiftExist == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIFT_EXISTS[0],
                'msg' => MsgCode::NO_SHIFT_EXISTS[1],
            ], 400);
        }


        $d1 =  $carbon->parse($date);
        $d2 =  $carbon->parse($date);
        $d1 = $d1->year . '-' . $d1->month . '-' . $d1->day . ' 00:00:00';
        $d2 = $d2->year . '-' . $d2->month . '-' . $d2->day . ' 23:59:59';


        $staff_ids = StaffInCalendar::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('start_time', '<=',  $d1)
            ->where('end_time', '>=', $d2)
            ->orderBy('id', 'asc')
            ->pluck('staff_id')->toArray();

        $staff_ids = array_unique($staff_ids);

        $calendar  = CalendarShift::create([
            'store_id' => $request->store->id,
            'branch_id' => $request->branch->id,
            'shift_id' => $shiftExist->id,
            'is_add' => false,
            "start_time" =>  $d1,
            "end_time" => $d2,
            "is_put_a_lot" => false,
        ]);

        foreach ($staff_ids  as $staff_id) {
            StaffInCalendar::create([
                'store_id' => $request->store->id,
                'branch_id' => $request->branch->id,
                'shift_id' => $shiftExist->id,
                'calendar_shift_id' =>  $calendar->id,
                "staff_id" =>  $staff_id,
                'is_add' => false,
                "start_time" =>  $d1,
                "end_time" => $d2,
                "is_put_a_lot" => false
            ]);
        }

        $calendar  = CalendarShift::create([
            'store_id' => $request->store->id,
            'branch_id' => $request->branch->id,
            'shift_id' => $shiftExist->id,
            'is_add' => true,
            "start_time" =>  $d1,
            "end_time" => $d2,
            "is_put_a_lot" => false,
        ]);
        foreach ($request->list_staff_ids  as $staff_id) {
            StaffInCalendar::create([
                'store_id' => $request->store->id,
                'branch_id' => $request->branch->id,
                'shift_id' => $shiftExist->id,
                'calendar_shift_id' =>  $calendar->id,
                "staff_id" => $staff_id,
                'is_add' => true,
                "start_time" =>  $d1,
                "end_time" => $d2,
                "is_put_a_lot" => false
            ]);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            // 'data' => "OK",
        ], 200);
    }
}
