<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Mail;
use App\Helper\Helper;
use App\Models\OtpCodeEmail;

/**
 * @group  Customer/Gửi otp email
 */
class CustomerSendMailOtpController extends Controller
{

    /**
     * Gửi otp qua email
     * @urlParam  store_code required Store code cần lấy.
     */
    public function send_email_otp(Request $request)
    {
        $email = $request->email ?? "x@x";

		$otp = Helper::generateRandomNum(6);
		$now = Helper::getTimeNowString();


		$otpExis = OtpCodeEmail::where('email', $email)->first();
		if ($otpExis == null) {
			OtpCodeEmail::create([
				"otp" =>  $otp,
				"email" => $email,
				"time_generate" => $now,
			]);
		} else {

			$otpExis->update([
				"otp" =>  $otp,
				"time_generate" => $now,
			]);
		}

        $data = $request->all();
        $emails = [$email];
        //Gửi mail

        Mail::to($emails)
            ->send(new \App\Mail\SendMailOTPCustomer($otp, $request->store));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
}
