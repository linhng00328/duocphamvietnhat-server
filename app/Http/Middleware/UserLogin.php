<?php

namespace App\Http\Middleware;

use App\Helper\Helper;
use App\Models\MsgCode;
use App\Models\PublicApiSession;
use App\Models\SessionStaff;
use App\Models\SessionUser;
use App\Models\Staff;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserLogin
{
    public function handle($request, Closure $next)
    {

        $token = request()->header('token');

        $checkTokenIsValidForUser = SessionUser::where('token', $token)->first();
        $checkTokenIsValidForStaff = SessionStaff::where('token', $token)->first();
        if (empty($token)) {
            return response()->json([
                'code' => 401,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
                'success' => false,
            ], 401);
        }

        if (empty($checkTokenIsValidForUser) && empty($checkTokenIsValidForStaff)) {
            $checkTokenIsValidPublicApi = PublicApiSession::where('token', $token)->first();

            $currenturl = $request->fullUrl();
            if ($checkTokenIsValidPublicApi  != null && (str_contains($currenturl, "customer") ||  str_contains($currenturl, "product") ||  str_contains($currenturl, "order"))) {
                $store = DB::table('stores')->where('id',  $checkTokenIsValidPublicApi->store_id)->first();
                $user = User::where('id', $store->user_id)->first();
                $request->merge([
                    'user' => $user,
                ]);
            } else {
                return response()->json([
                    'code' => 401,
                    'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                    'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                    'success' => false,
                ], 401);
            }
        }

        if ($checkTokenIsValidForUser) {
            $user = User::where('id', $checkTokenIsValidForUser->user_id)->first();
            if ($user == null) {
                $checkTokenIsValidForUser->delete();
                return response()->json([
                    'code' => 401,
                    'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                    'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                    'success' => false,
                ], 401);
            }

            User::where('id', $checkTokenIsValidForUser->user_id)->update([
                'last_visit_time' => Helper::getTimeNowString()
            ]);

            $request->merge([
                'user' => $user,
            ]);
        }

        if ($checkTokenIsValidForStaff) {
            $staff =  Staff::where('id', $checkTokenIsValidForStaff->staff_id)->first();
            if ($staff == null) {
                $checkTokenIsValidForStaff->delete();
                return response()->json([
                    'code' => 401,
                    'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                    'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                    'success' => false,
                ], 401);
            }
            $expiresAt = Carbon::now()->addMinutes(5);
            Cache::put('staff-is-online-' . $checkTokenIsValidForStaff->staff_id, true, $expiresAt);

            $store = Store::where('id', $staff->store_id)->first();

            User::where('id', $store->user_id)->update([
                'last_visit_time' => Helper::getTimeNowString()
            ]);

            $request->merge([
                'staff' =>  $staff,
            ]);
        }

        return $next($request);
    }
}
