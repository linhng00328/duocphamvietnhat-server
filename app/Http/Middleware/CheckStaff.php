<?php

namespace App\Http\Middleware;

use App\Models\MsgCode;
use Closure;
use Illuminate\Http\Request;


class CheckStaff
{

    public function handle(Request $request, Closure $next)
    {

        if ($request->staff == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ONLY_EMPLOYEES_CAN_ACCESS[0],
                'msg' => MsgCode::ONLY_EMPLOYEES_CAN_ACCESS[1],
            ], 400);

        }


        return $next($request);
    }
}
