<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\OperationHistory;
use App\Models\Store;
use Illuminate\Http\Request;


/**
 * @group  Thao tác
 */

class AdminHistoryOperationController extends Controller
{

    /**
     * Lịch sử thao tác
     * 
     * @bodyParam order_code string code đơn hàng
     * 
     */
    public function getAll(Request $request)
    {

        $histories = OperationHistory::when(request('function_type') != null, function ($query) {
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
            ->when(request('store_id') != null, function ($query) {
                $query->where('store_id', request('store_id'));
            })
            ->orderBy('id', 'desc')
            ->search(request('search') ?? null)
            ->paginate((request('limit') == null ? 20 : request('limit')));

        foreach ($histories  as $history) {
            $history->store = Store::where('id', $history->store_id)->first();
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $histories
        ], 200);
    }
}
