<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\SessionCustomer;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;


class CustomerLogin
{

    public function handle(Request $request, Closure $next)
    {

        $token = request()->header('customer-token');
        $code = $request->route()->parameter('store_code');

        if ($request->store != null) {
            $checkStoreExists = $request->store;
        } else {
            $checkStoreExists = Store::where(
                'store_code',
                $code
            )->first();
        }

        if (empty($checkStoreExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_EXISTS[1],
            ], 404);
        }


        $checkTokenIsValid = SessionCustomer::where('token', $token)->first();

        if ($checkTokenIsValid != null && $checkTokenIsValid->customer_id != null) {
            $c = Customer::where('id', $checkTokenIsValid->customer_id)
                ->where('official', true)
                ->where('store_id', $checkStoreExists->id)->first();
        }


        if (empty($token)) {

            return response()->json([
                'code' => 401,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
                'success' => false,
            ], 401);
        } else if (empty($checkTokenIsValid) ||  $c == null) {

            return response()->json([
                'code' => 401,
                'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                'success' => false,
            ], 401);
        } else {

            $request->merge([
                'customer' => $c,
            ]);

            return $next($request);
        }

        return $next($request);
    }
}
