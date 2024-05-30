<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Http\Controllers\Controller;
use App\Models\UserAdvice;
use App\Models\MsgCode;
use App\Models\OtpCodePhone;
use Illuminate\Http\Request;


/**
 * @group  Admin/Khách hàng cần tư vấn
 */
class OtpController extends Controller
{
    /**
     * Khách hàng cần tư vấn
     */
    public function get_otp(Request $request)
    {

        $phone = $request->phone_number;

        $otp = Helper::generateRandomNum(6);
        $now = Helper::getTimeNowString();
        $phone = PhoneUtils::convert($phone);
        $otpExis = OtpCodePhone::where('phone', $phone)->first();
        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $otpExis ,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    
}
