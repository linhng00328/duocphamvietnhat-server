<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\HistorySms;
use App\Models\LastSentOtp;
use App\Models\MsgCode;
use Illuminate\Http\Request;

class HistoryOtpController extends Controller
{

    public function getAll(Request $request)
    {

        $histories = HistorySms::where('store_id', $request->store->id)
            ->when(request('type') != null, function ($query) {
                $query->where('type', request('type'));
            })
            ->orderBy('id', 'desc')
            ->when(request('search') != null, function ($query) {
                $query->search(request('search'));
            })
            ->paginate(request('limit') ?? 20);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $histories
        ], 200);
    }
}
