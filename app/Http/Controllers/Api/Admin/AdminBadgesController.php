<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\Store;
use App\Models\User;
use App\Models\UserAdvice;
use Illuminate\Http\Request;

/**
 * @group  Admin/Badges
 */
class AdminBadgesController extends Controller
{
    /**
     * Lấy chỉ số
     */
    public function get_badges(Request $request)
    {
        $total_user_advice = 0;

        $total_user_advice_status_0 = 0;
        $total_user_advice_status_1 = 0;
        $total_user_advice_status_2 = 0;
        $total_user_advice_status_3 = 0;
        $total_user_advice_status_4 = 0;

        $is_admin = false;
        $is_employee = false;
        $total_employee = 0;

        $total_employee = 0;

        $total_employee  = Employee::count();
        if ($request->employee != null) {

            $is_employee = true;


            if ($request->employee->id_decentralization == 0) {
                $total_user_advice  = UserAdvice::count();
                $total_user_advice_status_0  = UserAdvice::where('status', 0)->count();
                $total_user_advice_status_1  = UserAdvice::where('status', 1)->count();
                $total_user_advice_status_2  = UserAdvice::where('status', 2)->count();
                $total_user_advice_status_3  = UserAdvice::where('status', 3)->count();
                $total_user_advice_status_4  = UserAdvice::where('status', 4)->count();

                $total_user_advice_status_5  = UserAdvice::where('status', 5)->count();
                $total_user_advice_status_6  = UserAdvice::where('status', 6)->count();
                $total_user_advice_status_7  = UserAdvice::where('status', 7)->count();

                $total_user_advice_status_all_consulting  = UserAdvice::whereIn('status', [1, 5, 6, 7])->count();
            } else {
                $total_user_advice  = UserAdvice::where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_0  = UserAdvice::where('status', 0)->where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_1  = UserAdvice::where('status', 1)->where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_2  = UserAdvice::where('status', 2)->where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_3  = UserAdvice::where('status', 3)->where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_4  = UserAdvice::where('status', 4)->where('id_employee_help', $request->employee->id)->count();

                $total_user_advice_status_5  = UserAdvice::where('status', 5)->where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_6  = UserAdvice::where('status', 6)->where('id_employee_help', $request->employee->id)->count();
                $total_user_advice_status_7  = UserAdvice::where('status', 7)->where('id_employee_help', $request->employee->id)->count();

                $total_user_advice_status_all_consulting  = UserAdvice::whereIn('status', [1, 5, 6, 7])->where('id_employee_help', $request->employee->id)->count();
            }
        }


        if ($request->admin != null) {

            $is_admin = true;


            $total_user_advice  = UserAdvice::count();
            $total_user_advice_status_0  = UserAdvice::where('status', 0)->count();
            $total_user_advice_status_1  = UserAdvice::where('status', 1)->count();
            $total_user_advice_status_2  = UserAdvice::where('status', 2)->count();
            $total_user_advice_status_3  = UserAdvice::where('status', 3)->count();
            $total_user_advice_status_4  = UserAdvice::where('status', 4)->count();

            $total_user_advice_status_5  = UserAdvice::where('status', 5)->count();
            $total_user_advice_status_6  = UserAdvice::where('status', 6)->count();
            $total_user_advice_status_7  = UserAdvice::where('status', 7)->count();

            $total_user_advice_status_all_consulting  = UserAdvice::whereIn('status', [1, 5, 6, 7])->count();
        }


        $timeNow = Helper::getTimeNowCarbon();


        $timeNow1 = Helper::getTimeNowCarbon();
        $next7days =  $timeNow1->addDays(7);

        $timeNow1 = Helper::getTimeNowCarbon();
        $next15days =  $timeNow1->addDays(15);

        $timeNow1 = Helper::getTimeNowCarbon();
        $next30days =  $timeNow1->addDays(30);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                'total_user_advice' => $total_user_advice,   //Tất cả
                'total_user_advice_status_0' => $total_user_advice_status_0, //cần tư vấn
                'total_user_advice_status_1' => $total_user_advice_status_1, // đang tư vấn cold
                'total_user_advice_status_2' => $total_user_advice_status_2, // đã chôts
                'total_user_advice_status_3' => $total_user_advice_status_3, //đã hủy
                'total_user_advice_status_4' => $total_user_advice_status_4, //free pos

                'total_user_advice_status_5' => $total_user_advice_status_5, // đang tư vấn warm
                'total_user_advice_status_6' => $total_user_advice_status_6, // đang tư vấn hot
                'total_user_advice_status_7' => $total_user_advice_status_7, // đang tư vấn non

                'total_user_advice_status_all_consulting' => $total_user_advice_status_all_consulting, // đang tư vấn non

                'employee' => $request->employee,
                'is_admin' => $is_admin,
                'is_employee' => $is_employee,
                'total_employee' => $total_employee,
                'id_decentralization' => $request->admin == null ? $request->employee->id_decentralization : -1,

                'count_register_in_day' => User::where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))
                    ->where('created_at', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_register_in_week' => User::where('created_at', '>=', Helper::get_begin_date_string(Helper::getStartOfWeek()))
                    ->where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_register_in_month' => User::where('created_at', '>=', Helper::get_begin_date_string(Helper::startOfMonth()))
                    ->where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_register_in_year' => User::where('created_at', '>=', Helper::get_begin_date_string(Helper::startOfYear()))
                    ->where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),

                'count_store_in_day' => Store::where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))
                    ->where('created_at', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_store_in_week' => Store::where('created_at', '>=', Helper::get_begin_date_string(Helper::getStartOfWeek()))
                    ->where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_store_in_month' => Store::where('created_at', '>=', Helper::get_begin_date_string(Helper::startOfMonth()))
                    ->where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_store_in_year' => Store::where('created_at', '>=', Helper::get_begin_date_string(Helper::startOfYear()))
                    ->where('created_at', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),

                'count_store_over_expriry_in_day' => Store::where('date_expried', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))
                    ->where('date_expried', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))->count(),


                'count_store_over_expriry_to_7_day' => Store::where('date_expried', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))
                    ->where('date_expried', '<=', Helper::get_end_date_string($next7days))->count(),
                'count_store_over_expriry_to_15_day' => Store::where('date_expried', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))
                    ->where('date_expried', '<=', Helper::get_end_date_string($next15days))->count(),
                'count_store_over_expriry_to_30_day' => Store::where('date_expried', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))
                    ->where('date_expried', '<=', Helper::get_end_date_string($next30days))->count(),


                'count_online_in_day' => User::where('last_visit_time', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))
                    ->where('last_visit_time', '>=', Helper::get_begin_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_online_in_week' => User::where('last_visit_time', '>=', Helper::get_begin_date_string(Helper::getStartOfWeek()))
                    ->where('last_visit_time', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_online_in_month' => User::where('last_visit_time', '>=', Helper::get_begin_date_string(Helper::startOfMonth()))
                    ->where('last_visit_time', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),
                'count_online_in_year' => User::where('last_visit_time', '>=', Helper::get_begin_date_string(Helper::startOfYear()))
                    ->where('last_visit_time', '<=', Helper::get_end_date_string(Helper::getTimeNowCarbon()))->count(),

            ]
        ], 200);
    }
}
