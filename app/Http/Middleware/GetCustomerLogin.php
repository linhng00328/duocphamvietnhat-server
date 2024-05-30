<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\SessionCustomer;
use Closure;
use Illuminate\Http\Request;


class GetCustomerLogin
{

    //Xử lý chuyển thông tin customer ko bắt buộc log
    public function handle(Request $request, Closure $next)
    {

        $token = request()->header('customer-token');


        $checkTokenIsValid = SessionCustomer::where('token', $token)->first();
        if (empty($token)) {
            return $next($request);
        } else if (empty($checkTokenIsValid)) {
            return $next($request);
        } else {
            $request->merge([
                'customer' => Customer::where('id', $checkTokenIsValid->customer_id)->first(),
            ]);
            return $next($request);
        }
        return $next($request);
    }
}
