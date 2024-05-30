<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\UserAdvice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  Admin/Báo cáo tư vấn sale
 */

class statisticConsultationController extends Controller
{
    /**
     * Tình trạng khách
     */
    public function statisticConsultation(Request $request)
    {
        $timeNow = Helper::getTimeNowCarbon();
        $today = $timeNow;
        $previous1days = (clone $timeNow)->addDays(-1);
        $previous2days = (clone $timeNow)->addDays(-2);
        $previous7days = (clone $timeNow)->addDays(-7);
        $previous15days = (clone $timeNow)->addDays(-15);
        $previous30days = (clone $timeNow)->addDays(-30);

        $reportConsultation = [];

        // $userAdviceStatus = DB::table('user_advice')->selectRaw('status, COUNT(*) as count');
        $userAdviceStatus = DB::table('user_advice');
        $adviceToday = (clone $userAdviceStatus)->whereDate('created_at', $today->format('Y-m-d'))->groupBy('status')->get();
        $advicePrevious1day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous1days->format('Y-m-d 00:00:00'))->groupBy('status')->get();
        $advicePrevious2day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous2days->format('Y-m-d 00:00:00'))->groupBy('status')->get();
        $advicePrevious7day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous7days->format('Y-m-d 00:00:00'))->groupBy('status')->get();
        $advicePrevious15day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous15days->format('Y-m-d 00:00:00'))->groupBy('status')->get();
        $advicePrevious30day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous30days->format('Y-m-d 00:00:00'))->groupBy('status')->get();
        $adviceTotal = (clone $userAdviceStatus)
            ->when($request->date_from, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->where('created_at', '<=', $request->date_to);
            })
            ->groupBy('status')->get();

        $listAdvice = [
            "today" => [$adviceToday, $today->format('Y-m-d')],
            "previous_1_days" => [$advicePrevious1day, $previous1days->format('Y-m-d')],
            "previous_2_days" => [$advicePrevious2day, $previous2days->format('Y-m-d')],
            "previous_7_days" => [$advicePrevious7day, $previous7days->format('Y-m-d')],
            "previous_15_days" => [$advicePrevious15day, $previous15days->format('Y-m-d')],
            "previous_30_days" => [$advicePrevious30day, $previous30days->format('Y-m-d')],
            "total" => [$adviceTotal, $today->format('Y-m-d')],
        ];

        foreach ($listAdvice as $keyAdvice => $advice) {
            $report = [
                "id" => Helper::getRandomOrderString(3),
                "name" => $keyAdvice,
                "date" => $advice[1],
                'data' => [
                    "phone_number" => 0,
                    "hot" => 0,
                    "warm" => 0,
                    "cold" => 0,
                    "del" => 0,
                    "done" => 0,
                    "none" => 0,
                    "have_phone_number_not_assign" => 0,
                ]
            ];

            foreach ($advice[0] as $keyAdviceDetail => $adviceDetail) {
                $report = [
                    "id" => Helper::getRandomOrderString(3),
                    "name" => $keyAdvice,
                    "date" => $advice[1],
                    'data' => [
                        "phone_number" => $report['data']['phone_number'] + 1,
                        "hot" => $report['data']['hot'] + ($adviceDetail->status == 6 ? $report['data']['hot'] + 1 : $report['data']['hot']),
                        "warm" => $report['data']['warm'] + ($adviceDetail->status == 5 ? $report['data']['warm'] + 1 : $report['data']['warm']),
                        "cold" => $report['data']['cold'] + ($adviceDetail->status == 1 ? $report['data']['cold'] + 1 : $report['data']['cold']),
                        "del" => $report['data']['del'] + ($adviceDetail->status == 3 ? $report['data']['del'] + 1 : $report['data']['del']),
                        "done" => $report['data']['done'] + ($adviceDetail->status == 2 ? $report['data']['done'] + 1 : $report['data']['done']),
                        "none" => $report['data']['none'] + ($adviceDetail->status == 7 ? $report['data']['none'] + 1 : $report['data']['none']),
                        "have_phone_number_not_assign" => $report['data']['have_phone_number_not_assign'] + ($adviceDetail->id_employee_help != null ? $report['data']['have_phone_number_not_assign'] + 1 : $report['data']['have_phone_number_not_assign']),
                    ]
                ];
            }
            array_push($reportConsultation, $report);
        };

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $reportConsultation,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    //bn trung sdt total
    // total success, total fail, total
    /**
     * Tổng số phụ trách
     */
    public function supportStatisticConsultation(Request $request)
    {
        $timeNow = Helper::getTimeNowCarbon();
        $today = $timeNow;
        $previous1days = (clone $timeNow)->addDays(-1);
        $previous2days = (clone $timeNow)->addDays(-2);
        $previous7days = (clone $timeNow)->addDays(-7);
        $previous15days = (clone $timeNow)->addDays(-15);
        $previous30days = (clone $timeNow)->addDays(-30);

        $reportConsultation = [];

        $userAdviceStatus = DB::table('user_advice')->select('id_employee_help', DB::raw('count(*) as count'));
        $adviceToday = (clone $userAdviceStatus)->whereDate('created_at', $today->format('Y-m-d'))->groupBy('status')->get();
        $advicePrevious1day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous1days->format('Y-m-d 00:00:00'))->groupBy('id_employee_help')->get();
        $advicePrevious2day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous2days->format('Y-m-d 00:00:00'))->groupBy('id_employee_help')->get();
        $advicePrevious7day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous7days->format('Y-m-d 00:00:00'))->groupBy('id_employee_help')->get();
        $advicePrevious15day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous15days->format('Y-m-d 00:00:00'))->groupBy('id_employee_help')->get();
        $advicePrevious30day = (clone $userAdviceStatus)->where('created_at', '<=', $today->format('Y-m-d 23:59:59'))->where('created_at', '>=', $previous30days->format('Y-m-d 00:00:00'))->groupBy('id_employee_help')->get();
        $adviceTotal = DB::table('user_advice')
            ->when($request->date_from, function ($query) use ($request) {
                $query->where('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->where('created_at', '<=', $request->date_to);
            })
            ->select('id_employee_help', 'status', DB::raw('count(status) as count'))->groupBy('id_employee_help')->get();
        $adviceStatusHot = DB::table('user_advice')->select('id_employee_help', DB::raw('count(status) as count'))->where('status', 6)->groupBy('id_employee_help')->get();
        $adviceStatusWarm = DB::table('user_advice')->select('id_employee_help', DB::raw('count(status) as count'))->where('status', 5)->groupBy('id_employee_help')->get();
        $adviceStatusCold = DB::table('user_advice')->select('id_employee_help', DB::raw('count(status) as count'))->where('status', 1)->groupBy('id_employee_help')->get();
        $adviceStatusDel = DB::table('user_advice')->select('id_employee_help', DB::raw('count(status) as count'))->where('status', 3)->groupBy('id_employee_help')->get();
        $adviceStatusDone = DB::table('user_advice')->select('id_employee_help', DB::raw('count(status) as count'))->where('status', 2)->groupBy('id_employee_help')->get();
        $adviceStatusNone = DB::table('user_advice')->select('id_employee_help', DB::raw('count(status) as count'))->where('status', 7)->groupBy('id_employee_help')->get();

        $listAdvice = [
            "previous_2_days" => $advicePrevious2day,
            "previous_1_days" => $advicePrevious1day,
            "today" => $adviceToday,
            "previous_7_days" => $advicePrevious7day,
            "previous_15_days" => $advicePrevious15day,
            "previous_30_days" => $advicePrevious30day,
            "total" => $adviceTotal,
            "hot" => $adviceStatusHot,
            "warm" => $adviceStatusWarm,
            "cold" => $adviceStatusCold,
            "del" => $adviceStatusDel,
            "done" => $adviceStatusDone,
            "none" => $adviceStatusNone,
        ];

        $employees = Employee::select('id', 'name')->get();
        $listEmployee = [];
        $formEmployee = [];
        $reportConsultation = [];

        array_push($listEmployee, [
            "key" => null
        ]);

        foreach ($employees as $employee) {
            array_push($listEmployee, [
                'id' => $employee->id,
                'name' => $employee->name,
            ]);

            array_push($formEmployee, [
                'user_id' => $employee->id,
                'user_name' => $employee->name,
                'value' => 0,
            ]);
        }
        $tempForm = $formEmployee;

        foreach ($listAdvice as $adviceId => $advice) {
            $formEmployee = $tempForm;

            foreach ($formEmployee as &$employee) {
                foreach ($advice as $adviceItem) {
                    if ($employee['user_id'] == $adviceItem->id_employee_help) {

                        $employee['value'] += 1;
                    }
                }
            }
            $form = [
                "name" => $adviceId,
                "data" => $formEmployee
            ];
            array_push($reportConsultation, $form);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $reportConsultation,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
