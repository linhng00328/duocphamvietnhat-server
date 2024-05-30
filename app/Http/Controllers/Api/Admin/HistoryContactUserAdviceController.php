<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoryContactUserAdvice;
use App\Models\MsgCode;
use App\Models\UserAdvice;
use Illuminate\Http\Request;

/**
 * @group  Admin/Lịch sử liên hệ user tư vấn

 */

class HistoryContactUserAdviceController extends Controller
{
    /**
     * Thêm vào lịch sử
     * @bodyParam note string required nội dung tư vấn
     * @bodyParam status int required trạng thái tư vấn 0 chưa liên hệ dc, 1 khách đang bận, 2 khách chần chừ, 3 hẹn bữa sau, 4 đã ok, 5 khách hết quan tâm
     */
    public function create(Request $request, $user_advice_id)
    {

        if ($request->employee == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ONLY_EMPLOYEE[0],
                'msg' => MsgCode::ONLY_EMPLOYEE[1],
            ], 400);
        }

        $userAdvice = UserAdvice::where('id', '=', $user_advice_id)->first();
        if ($userAdvice == null) {
            return response()->json([
                'code' => 400,
                'success' => true,
                'msg_code' => MsgCode::INVALID_USERNAME[0],
                'msg' => MsgCode::INVALID_USERNAME[1],
            ], 400);
        }


        $created = HistoryContactUserAdvice::create([
            'user_advice_id' => $user_advice_id,
            'employee_id' => $request->employee->id,
            'note' => $request->note,
            'status' => $request->status,
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => HistoryContactUserAdvice::where('id', $created->id)->first(),
        ], 200);
    }

    /**
     * Danh sách lịch sử của 1 user
     * 
     * @urlParam  user_advice_id required id user cần hỗ trợ
     */
    public function getAll(Request $request, $user_advice_id)
    {


        $histories = HistoryContactUserAdvice::where('user_advice_id', $user_advice_id)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $histories
        ], 200);
    }
}
