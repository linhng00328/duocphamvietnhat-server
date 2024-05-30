<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PointHistory;
use Illuminate\Http\Request;

/**
 * @group  Customer/Tích điểm
 */
class CustomerPointHistoryController extends Controller
{
    /**
     * Lịch sử tích điểm
     */
    public function getAll(Request $request, $id)
    {
        $Point_history = PointHistory::where(
            'store_id',
            $request->store->id
        )
            ->where(
                'customer_id',
                $request->customer->id
            )->orderBy('id', 'DESC')
            ->paginate(20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $Point_history,
        ], 200);
    }
}
