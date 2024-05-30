<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\OperationHistory;
use Illuminate\Http\Request;


/**
 * @group  Thao tác
 */

class HistoryOperationController extends Controller
{

    /**
     * Lịch sử thao tác
     * 
     * @bodyParam order_code string code đơn hàng
     * 
     */
    public function getAll(Request $request)
    {
        $histories = OperationHistory::where('store_id', $request->store->id)
            ->when(request('function_type') != null, function ($query) {
                $query->where('function_type', request('function_type'));
            })
            ->when(request('branch_id') != null, function ($query) {
                $query->where('branch_id', request('branch_id'));
            })
            ->when(request('action_type') != null, function ($query) {
                $query->where('action_type', request('action_type'));
            })
            ->when(request('staff_id') != null, function ($query) {
                $query->where('staff_id', request('staff_id'))
                    ->orWhere('user_id', request('staff_id'));
            })
            ->when(request('user_id') != null, function ($query) {
                $query->where('user_id', request('user_id'));
            })
            ->orderBy('id', 'desc')
            ->search(request('search') ?? null)
            ->paginate((request('limit') == null ? 20 : request('limit')));


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $histories
        ], 200);
    }
}
