<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\ShiftUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\ProductShifts;
use App\Models\Shift;
use Illuminate\Http\Request;

/**
 * @group  User/Ca làm việc
 */

class ShiftController extends Controller
{
    /**
     * 
     * Danh sách ca làm việc
     * 
     */
    public function getAll(Request $request, $id)
    {

        $search = request('search');

        $shifts = Shift::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->when(!empty($search), function ($query) use ($search, $request) {
                $query->search($search);
            })
            ->paginate(request('limit') == null ? 20 : request('limit'))
           ;


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $shifts,
        ], 200);
    }


    /**
     * xóa một ca
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  shift_id required ID ca cần xóa
     */
    public function delete(Request $request, $id)
    {

        $id = $request->route()->parameter('shift_id');
        $checkShiftExists = Shift::where(
            'id',
            $id
        )->where('branch_id', $request->branch->id)
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($checkShiftExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIFT_EXISTS[0],
                'msg' => MsgCode::NO_SHIFT_EXISTS[1],
            ], 404);
        } else {
            $idDeleted = $checkShiftExists->id;
            $checkShiftExists->delete();
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => ['idDeleted' => $idDeleted],
            ], 200);
        }
    }

    /**
     * xem 1 một ca
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  shift_id required ID ca cần xóa
     */
    public function getOne(Request $request, $id)
    {

        $id = $request->route()->parameter('shift_id');
        $checkShiftExists = Shift::where(
            'id',
            $id
        )->where('branch_id', $request->branch->id)
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($checkShiftExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIFT_EXISTS[0],
                'msg' => MsgCode::NO_SHIFT_EXISTS[1],
            ], 404);
        } else {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $checkShiftExists,
            ], 200);
        }
    }

    /**
     * Tạo một Ca
     * @urlParam  store_code required Store code cần update
     * @urlParam  shift_id required shift_id cần update
     * @bodyParam name String Tên ca
     * @bodyParam code String Mã ca
     * @bodyParam start_work_hour int Giờ bắt đầu 
     * @bodyParam start_work_minute int Phút bắt đầu
     * @bodyParam end_work_hour int  giờ kết thúc
     * @bodyParam end_work_minute int phút kết thúc
     * @bodyParam start_break_hour int giờ nghỉ bắt đầu
     * @bodyParam start_break_minute int phút nghỉ bắt đầu
     * @bodyParam end_break_hour int giờ nghỉ kết thúc
     * @bodyParam end_break_minute int phút nghit bắt đầu
     * @bodyParam minutes_late_allow int  phút đi trễ cho phép
     * @bodyParam minutes_early_leave_allow int  phút đi về sớm cho
     * @bodyParam days_of_week List ngày trong tuần VD: [2,3,4,5,6,7]
     *
     */
    public function create(Request $request)
    {

        if (empty($request->name)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 404);
        }

        $shiftNameExists = Shift::where(
            'name',
            $request->name
        )->where('branch_id', $request->branch->id)
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($shiftNameExists  != null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 404);
        }


        if ($this->check_time(
            $request->start_work_hour,
            $request->start_work_minute,
            $request->end_work_hour,
            $request->end_work_minute
        ) != null) {
            return $this->check_time(
                $request->start_work_hour,
                $request->start_work_minute,
                $request->end_work_hour,
                $request->end_work_minute
            );
        }


        $has_break = false;



        if (
            $request->start_break_hour == 0 && $request->start_break_minute == 0 &&
            $request->end_break_hour == 0 && $request->end_break_minute == 0
        ) {
        } else {
            if (
                !is_null($request->start_break_hour) &&
                !is_null($request->start_break_minute) &&
                !is_null($request->end_break_hour) &&
                !is_null($request->end_break_minute)
            ) {



                if ($this->check_time(
                    $request->start_break_hour,
                    $request->start_break_minute,
                    $request->end_break_hour,
                    $request->end_break_minute
                ) != null) {

                    return $this->check_time(
                        $request->start_break_hour,
                        $request->start_break_minute,
                        $request->end_break_hour,
                        $request->end_break_minute
                    );
                }

                $has_break = true;
            }
        }



        $days_of_week = [];
        if (!is_array($request->days_of_week)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DAY[0],
                'msg' => MsgCode::INVALID_DAY[1],
            ], 400);
        } else {
            foreach ($request->days_of_week as $day) {

                if (!in_array($day, [2, 3, 4, 5, 6, 7, 8])) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DAY[0],
                        'msg' => MsgCode::INVALID_DAY[1],
                    ], 400);
                }


                if (!in_array($day, $days_of_week)) {
                    array_push($days_of_week, $day);
                }
            }
        }

        $shiftCreate = Shift::create([
            'store_id' => $request->store->id,

            'branch_id' => $request->branch->id,

            "name" => $request->name,
            "code" => $request->code,

            "start_work_hour" => $request->start_work_hour,
            "start_work_minute" => $request->start_work_minute,
            "end_work_hour" => $request->end_work_hour,
            "end_work_minute" => $request->end_work_minute,

            "start_break_hour" => $has_break == true ?  $request->start_break_hour : null,
            "start_break_minute" => $has_break == true ?  $request->start_break_minute : null,
            "end_break_hour" => $has_break == true ?  $request->end_break_hour : null,
            "end_break_minute" =>  $has_break == true ? $request->end_break_minute : null,

            "minutes_late_allow" => $request->minutes_late_allow,
            "minutes_early_leave_allow" => $request->minutes_early_leave_allow,
            "days_of_week" => json_encode($days_of_week),

        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Shift::where('id', $shiftCreate->id)->first(),
        ], 200);
    }


    /**
     * update một Ca
     * @urlParam  store_code required Store code cần update
     * @urlParam  shift_id required shift_id cần update
     * @bodyParam name String Tên ca
     * @bodyParam code String Mã ca
     * @bodyParam start_work_hour int Giờ bắt đầu 
     * @bodyParam start_work_minute int Phút bắt đầu
     * @bodyParam end_work_hour int  giờ kết thúc
     * @bodyParam end_work_minute int phút kết thúc
     * @bodyParam start_break_hour int giờ nghỉ bắt đầu
     * @bodyParam start_break_minute int phút nghỉ bắt đầu
     * @bodyParam end_break_hour int giờ nghỉ kết thúc
     * @bodyParam end_break_minute int phút nghit bắt đầu
     * @bodyParam minutes_late_allow int  phút đi trễ cho phép
     * @bodyParam minutes_early_leave_allow int  phút đi về sớm cho
     * @bodyParam days_of_week List ngày trong tuần VD: [2,3,4,5,6,7]
     * 
     */
    public function update(Request $request)
    {

        $id = $request->route()->parameter('shift_id');
        $shiftExists = Shift::where(
            'id',
            $id
        )->where('branch_id', $request->branch->id)
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if (empty($shiftExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SHIFT_EXISTS[0],
                'msg' => MsgCode::NO_SHIFT_EXISTS[1],
            ], 404);
        } else {

            if (empty($request->name)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                    'msg' => MsgCode::NAME_IS_REQUIRED[1],
                ], 404);
            }

            $shiftNameExists = Shift::where(
                'name',
                $request->name
            )
                ->where('id', '!=',  $id)
                ->where('branch_id', $request->branch->id)
                ->where(
                    'store_id',
                    $request->store->id
                )->first();

            if ($shiftNameExists  != null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                    'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
                ], 404);
            }


            if ($this->check_time(
                $request->start_work_hour,
                $request->start_work_minute,
                $request->end_work_hour,
                $request->end_work_minute
            ) != null) {
                return $this->check_time(
                    $request->start_work_hour,
                    $request->start_work_minute,
                    $request->end_work_hour,
                    $request->end_work_minute
                );
            }

            $has_break = false;


            if (
                $request->start_break_hour == 0 && $request->start_break_minute == 0 &&
                $request->end_break_hour == 0 && $request->end_break_minute == 0
            ) {
            } else {
                if (
                    !is_null($request->start_break_hour) &&
                    !is_null($request->start_break_minute) &&
                    !is_null($request->end_break_hour) &&
                    !is_null($request->end_break_minute)
                ) {
                    if ($this->check_time(
                        $request->start_break_hour,
                        $request->start_break_minute,
                        $request->end_break_hour,
                        $request->end_break_minute
                    ) != null) {
                        return $this->check_time(
                            $request->start_break_hour,
                            $request->start_break_minute,
                            $request->end_break_hour,
                            $request->end_break_minute
                        );
                    }

                    $has_break = true;
                }
            }


            $days_of_week = [];
            if (!is_array($request->days_of_week)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DAY[0],
                    'msg' => MsgCode::INVALID_DAY[1],
                ], 400);
            } else {
                foreach ($request->days_of_week as $day) {

                    if (!in_array($day, [2, 3, 4, 5, 6, 7, 8])) {
                        return response()->json([
                            'code' => 400,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_DAY[0],
                            'msg' => MsgCode::INVALID_DAY[1],
                        ], 400);
                    }


                    if (!in_array($day, $days_of_week)) {
                        array_push($days_of_week, $day);
                    }
                }
            }



            $shiftExists->update(
                [
                    'store_id' => $request->store->id,

                    'branch_id' => $request->branch->id,

                    "name" => $request->name,
                    "code" => $request->code,

                    "start_work_hour" => $request->start_work_hour,
                    "start_work_minute" => $request->start_work_minute,
                    "end_work_hour" => $request->end_work_hour,
                    "end_work_minute" => $request->end_work_minute,

                    "start_break_hour" => $has_break == true ?  $request->start_break_hour : null,
                    "start_break_minute" => $has_break == true ?  $request->start_break_minute : null,
                    "end_break_hour" => $has_break == true ?  $request->end_break_hour : null,
                    "end_break_minute" =>  $has_break == true ? $request->end_break_minute : null,

                    "minutes_late_allow" => $request->minutes_late_allow,
                    "minutes_early_leave_allow" => $request->minutes_early_leave_allow,
                    "days_of_week" => json_encode($days_of_week),
                ]
            );

            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => Shift::where('id', $id)->first(),
            ], 200);
        }
    }

    function check_time($start_hour,  $start_minute, $end_hour, $end_minute)
    {

        if (
            $start_hour === null
            ||
            $start_minute === null
        ) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::START_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::START_TIME_IS_REQUIRED[1],
            ], 404);
        }

        if (
            $end_hour === null &&
            $end_minute === null
        ) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_IS_REQUIRED[1],
            ], 404);
        }




        if (
            ShiftUtils::checkHour($start_hour) == false ||
            ShiftUtils::checkMinute($start_minute) == false  ||
            ShiftUtils::checkHour($end_hour) == false ||
            ShiftUtils::checkMinute($end_minute) == false
        ) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 404);
        }

        $type = ShiftUtils::compareTimeWork(
            $start_hour,
            $start_minute,
            $end_hour,
            $end_minute,
        );

        if ($type != ShiftUtils::START_LESS_END) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::END_TIME_GREATE_START_TIME_IS_REQUIRED[0],
                'msg' => MsgCode::END_TIME_GREATE_START_TIME_IS_REQUIRED[1],
            ], 404);
        }

        return null;
    }
}
