<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\LoggerFailJob;
use App\Models\MsgCode;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

/**
 * @group  Admin/Log
 */
class LoggerFailController extends Controller
{
    /**
     * Thông tin server
     */
    public function log(Request $request)
    {
        if (!empty($request->log)) {
            LoggerFailJob::dispatch(
                $request->log,
            );
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
