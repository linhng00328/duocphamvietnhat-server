<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\CheckinCheckoutUtils;
use App\Http\Controllers\Controller;
use App\Models\DateTimekeeping;
use App\Models\DateTimekeepingHistory;
use App\Models\MobileCheckin;
use App\Models\MsgCode;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;


/**
 * @group User/Quản lý chấm công
 * 
 * APIs AppTheme
 */
class CheckTimeKeepingController extends Controller
{

    /**
     * Bổ sung - bớt công
     * @urlParam  store_code required Store code
     * 
     * @bodyParam is_bonus boolean Thêm công (true là thêm, false là bớt)
     * @bodyParam checkin_time datetime checkin_time Thời gian bắt đầu
     * @bodyParam checkout_time datetime checkout_time Thời gian kết thúc
     * @bodyParam reason string Lý do
     * @bodyParam staff_id int Staff id
     * 
     */
    public function bonus_less_checkin_checkout(Request $request)
    {
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($request->checkin_time);
        $date2 = $carbon->parse($request->checkout_time);


        if (
            Staff::where('store_id', $request->store->id)
            ->where('id', $request->staff_id)
            ->first() == null
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 400);
        }


        $staff =  Staff::where('store_id', $request->store->id)
            ->where('id', $request->staff_id)
            ->first();

        if (
            $request->checkin_time == null || $request->checkout_time == null ||

            $date1 >  $date2 || $date1 == $date2 || $date2->diffInDays($date1) > 0
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }

        $date = $date1->year . '-' . $date1->month . '-' . $date1->day;

        $is_bonus = filter_var($request->is_bonus, FILTER_VALIDATE_BOOLEAN);

        $dateTimekeeping  = DateTimekeeping::where('store_id', $request->store->id)

            ->where('branch_id',  $request->branch->id)
            ->where('staff_id',  $staff->id)
            ->where('date', $date)->first();


        if ($dateTimekeeping == null) {
            $dateTimekeeping  = DateTimekeeping::create([
                "store_id" =>  $request->store->id,
                "branch_id" =>  $request->branch->id,
                "staff_id" => $request->staff_id,
                "salary_one_hour" =>  $staff->salary_one_hour,
                "date"  => $date,
            ]);
        } else {
            $dateTimekeeping->update([
                "store_id" =>  $request->store->id,
                "branch_id" =>  $request->branch->id,
                "staff_id" => $request->staff_id,
                "salary_one_hour" =>  $staff->salary_one_hour,
                "date"  => $date,
            ]);
        }

        $checkin = DateTimekeepingHistory::create([
            "store_id" =>  $request->store->id,
            "branch_id" =>  $request->branch->id,
            "staff_id" => $request->staff_id,
            "date_timekeeping_id" => $dateTimekeeping->id,
            "time_check" => $date1,
            "is_checkin" => true,
            "is_bonus" => $is_bonus,
            "status" => CheckinCheckoutUtils::STATUS_CHECKED,
            "note" => $request->note,
            "checkout_for_checkin_id" => null,
            "remote_timekeeping" => filter_var($request->is_remote, FILTER_VALIDATE_BOOLEAN),
            "reason" => $request->reason,
            "date"  => $date,
            'from_user' => true,
            'from_staff_id' => $request->staff == null ? null :  $request->staff->id,
            'from_user_id' => $request->user == null ? null :  $request->user->id,
        ]);

        $checkout = DateTimekeepingHistory::create([
            "store_id" =>  $request->store->id,
            "branch_id" =>  $request->branch->id,
            "staff_id" => $request->staff_id,
            "date_timekeeping_id" => $dateTimekeeping->id,
            "time_check" => $date2,
            "is_checkin" => false,
            "is_bonus" => $is_bonus,
            "status" => CheckinCheckoutUtils::STATUS_CHECKED,
            "note" => $request->note,
            "checkout_for_checkin_id" =>  $checkin->id,
            "remote_timekeeping" => filter_var($request->is_remote, FILTER_VALIDATE_BOOLEAN),
            "reason" => $request->reason,
            "date"  => $date,
            'from_user' => true,
            'from_staff_id' => $request->staff == null ? null :  $request->staff->id,
            'from_user_id' => $request->user == null ? null :  $request->user->id,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Danh sách yêu cầu chấm công
     * @urlParam  store_code required Store code
     */
    public function getAllAwaitCheckinCheckout(Request $request)
    {
        $search = request('search');
        $his  = DateTimekeepingHistory::where('date_timekeeping_histories.store_id', $request->store->id)
            ->where('date_timekeeping_histories.branch_id', $request->branch->id)
            ->where('date_timekeeping_histories.status', CheckinCheckoutUtils::STATUS_AWAIT_CHECK)
            ->orderBy('date_timekeeping_histories.id', 'desc')
            ->when(!empty($search), function ($query) use ($search, $request) {
                $query->search($search);
            })
            ->paginate(request('limit') == null ? 20 : request('limit'));



        foreach ($his  as $h) {
            $h->staff =   Staff::select('name', 'id', 'phone_number')->where('id',  $h->staff_id)->first();
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $his
        ], 200);
    }

    /**
     * Thay đổi trạng thái chấm công
     * 
     * Trạng thái    STATUS_AWAIT_CHECK = 1;STATUS_CHECKED = 2; STATUS_CANCEL = 3;
     * 
     * @urlParam  store_code required Store code
     * @bodyParam status  int trạng thái 1 chờ xử lý, 2 đã đồng ý, 3 hủy
     * 
     * 
     */
    public function changeStatus(Request $request)
    {
        $id = $request->route()->parameter('date_timekeeping_history_id');

        $his  = DateTimekeepingHistory::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('id',  $id)->first();

        if ($his  == null) {
            return response()->json([
                'code' => false,
                'success' => true,
                'msg_code' => MsgCode::NO_CHECKIN_CHECKOUT_HISTORY_EXISTS[0],
                'msg' => MsgCode::NO_CHECKIN_CHECKOUT_HISTORY_EXISTS[1],
            ], 404);
        }

        if (is_null($request->status)) {
            return response()->json([
                'code' => false,
                'success' => true,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ], 404);
        }

        $his->update([
            'status' => $request->status
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Danh sách mobile yêu cầu
     * @urlParam  store_code required Store code
     * @bodyParam status  int trạng thái 0 chờ xử lý, 1 đã đồng ý, 2 hủy
     */
    public function getAllAwaitMobile(Request $request)
    {

        $search = request('search');
        $mobile  = MobileCheckin::where('mobile_checkins.store_id', $request->store->id)
            ->where('mobile_checkins.branch_id', $request->branch->id)
            ->where('mobile_checkins.status', 0)
            ->when(!empty($search), function ($query) use ($search, $request) {
                $query->search($search);
            })
            ->orderBy('id', 'desc')
            ->get();


        foreach ($mobile as $m) {

            $staff = Staff::select('name', 'id', 'phone_number')->where('id', $m->staff_id)->first();
            $staff->total_device = MobileCheckin::where('store_id', $request->store->id)
                ->where('status', 1)
                ->where('branch_id', $request->branch->id)->where('staff_id', $m->staff_id)->count();
            $m->staff =  $staff;
        }
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $mobile
        ], 200);
    }

    /**
     * Thay đổi trạng thái mobile
     * 
     * Trạng thái    0 cho duyet, 1 da duyet
     * 
     * @urlParam  store_code required Store code
     * @bodyParam status  int trạng thái 1 chờ xử lý, 1 da duyet
     * 
     * 
     */
    public function changeStatusMobileCheckin(Request $request)
    {
        $id = $request->route()->parameter('mobile_id');

        $mobile  = MobileCheckin::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('id',  $id)->first();

        if ($mobile  == null) {
            return response()->json([
                'code' => false,
                'success' => true,
                'msg_code' => MsgCode::NO_MOBILE_EXISTS[0],
                'msg' => MsgCode::NO_MOBILE_EXISTS[1],
            ], 404);
        }

        if (is_null($request->status)) {
            return response()->json([
                'code' => false,
                'success' => true,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ], 404);
        }

        $mobile->update([
            'status' => $request->status
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Danh sách mobile của nhân viên
     * @urlParam  store_code required Store code
     * @urlParam  staff_id required staff_id
     */
    public function getAllMobileOfStaff(Request $request)
    {
        $staff_id = $request->route()->parameter('staff_id');

        if (Staff::where('id', $staff_id)->where('branch_id', $request->branch->id)->first() == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STAFF_EXISTS[0],
                'msg' => MsgCode::NO_STAFF_EXISTS[1],
            ], 404);
        }

        $mobile  = MobileCheckin::where('store_id', $request->store->id)
            ->where('branch_id', $request->branch->id)
            ->where('status', 1)
            ->where('staff_id',  $staff_id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $mobile
        ], 200);
    }
}
