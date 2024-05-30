<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\SessionAdmin;
use App\Models\SessionEmployee;
use Closure;
use Illuminate\Http\Request;


class AdminLogin
{

    public function handle(Request $request, Closure $next)
    {


        $token = request()->header('admin-token');

        $checkTokenIsValid = SessionAdmin::where('token', $token)->first();
        $checkTokenIsValidForEmployee = SessionEmployee::where('token', $token)->first();
        if (empty($token)) {

            return response()->json([
                'code' => 401,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
                'success' => false,
            ], 401);
        } else if (empty($checkTokenIsValid) && empty($checkTokenIsValidForEmployee)) {
            return response()->json([
                'code' => 401,
                'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                'success' => false,
            ], 401);
        } else {

            if ($checkTokenIsValid) {
                $request->merge([
                    'admin' => Admin::where('id', $checkTokenIsValid->admin_id)->first(),
                ]);
            }

            if ($checkTokenIsValidForEmployee) {
                $request->merge([
                    'employee' => Employee::where('id', $checkTokenIsValidForEmployee->employee_id)->first(),
                ]);
            }


            return $next($request);
        }

        return $next($request);
    }
}
