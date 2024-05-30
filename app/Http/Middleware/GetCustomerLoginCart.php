<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\SessionCustomer;
use Closure;
use Illuminate\Http\Request;


class GetCustomerLoginCart
{

    //Xử lý chuyển thông tin customer ko bắt buộc log
    public function handle(Request $request, Closure $next)
    {

        $token = request()->header('customer-token');
        $device_id = request()->header('device-id');


        $checkTokenIsValid = SessionCustomer::where('token', $token)->first();
        if ( (empty($token) || (empty($checkTokenIsValid))) &&  (empty($device_id))) {
            return response()->json([
                'code'=> 401,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
                'success' => false,
            ]);
        } 
        
        else {
            $request->merge([
                'device_id' => $device_id,
                'customer' =>  $checkTokenIsValid==null ?null : Customer::where('id', $checkTokenIsValid->customer_id)->first(),
            ]);
            return $next($request);
        }
        return $next($request);
    }
}
