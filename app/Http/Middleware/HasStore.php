<?php

namespace App\Http\Middleware;

use App\Models\AppTheme;
use App\Models\MsgCode;
use App\Models\SessionUser;
use App\Models\Store;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class HasStore
{
    public function handle($request, Closure $next)
    {
        $code = $request->route()->parameter('store_code');
        $customer_id_post = $request->customer_id;

        if ($request->user != null) {
            $checkStoreExists = Store::where(
                'store_code',
                $code
            )->where(
                'user_id',
                $request->user->id
            )->first();

            if (empty($checkStoreExists)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                    'msg' => MsgCode::NO_STORE_EXISTS[1],
                ], 404);
            } else {

                $request->merge([
                    'store' => $checkStoreExists,
                    'customer_id_post'  =>     $customer_id_post
                ]);

                return $next($request);
            }
        }



        if ($request->staff != null) {
            $checkStoreExists = Store::where(
                'id',
                $request->staff->store_id,
            )->first();

            if (empty($checkStoreExists)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STORE_EXISTS[0],
                    'msg' => MsgCode::NO_STORE_EXISTS[1],
                ], 404);
            } else {

                $request->merge([
                    'store' => $checkStoreExists,
                ]);

                return $next($request);
            }
        }
    }
}
