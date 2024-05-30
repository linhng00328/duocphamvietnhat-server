<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\PhoneUtils;
use App\Http\Controllers\Api\HandleReceiverSmsController;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\User;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use App\Models\OtpCodePhone;
use App\Models\UserAdvice;
use Illuminate\Http\Request;


/**
 * @group  User/Đăng ký
 */
class RegisterController extends Controller
{
    /**
     * Register
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam email string required Email
     * @bodyParam password string required Password
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     * @bodyParam otp_from string  phone(từ sdt)  email(từ email) mặc định là phone
     */
    public function register(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $otp = $request->otp;
        $email = $request->email;
        // if (
        //     $phone == null &&
        //     PhoneUtils::check_valid($phone) == false
        // ) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
        //         'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
        //     ], 400);
        // }



        if (User::where('phone_number', $phone)->exists()) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 409);
        }


        if (!empty($request->email)) {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_EMAIL[0],
                    'msg' => MsgCode::INVALID_EMAIL[1],
                ], 400);
            }
        }


        if (
            !empty($request->email) &&
            $request->email != null &&
            strlen($request->email) > 0 && User::where('email', $request->email)->exists()
        ) {

            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS[0],
                'msg' => MsgCode::EMAIL_ALREADY_EXISTS[1],
            ], 409);
        }

        if (
            strlen($request->password) < 6
        ) {

            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
            ], 400);
        }


        $from = "";
        $type = "";
        if ($request->otp_from == "email") {
            $from = $email;
            $type = "email";
            $otpExis = OtpCodeEmail::where('email', $email)
                ->where('otp', $otp)
                ->first();
            if ($otpExis == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OTP[0],
                    'msg' => MsgCode::INVALID_OTP[1],
                ], 400);
            }
        } else {
            $from = $phone;
            $type = "phone";
            $otpExis = OtpCodePhone::where('phone', $phone)
                ->where('otp', $otp)
                ->first();
            if ($otpExis == null) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OTP[0],
                    'msg' => MsgCode::INVALID_OTP[1],
                ], 400);
            }
        }


        if (HandleReceiverSmsController::has_expired_otp($from, $type)) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXPIRED_PIN_CODE[0],
                'msg' => MsgCode::EXPIRED_PIN_CODE[1],
            ], 400);
        }

        HandleReceiverSmsController::reset_otp($phone);

        $userCreate = User::create(
            [
                'name' =>  $request->name,
                'area_code' => "+84",
                'phone_number' => $phone,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'create_maximum_store' => 1
            ]
        );


        if (UserAdvice::where('phone_number', $phone)->first() == null) {
            $userAdvice_created = UserAdvice::create([
                'area_code' => '+84',
                'username' => $request->name,
                'phone_number' => $phone,
                'email' => $request->email,
                'name' => $request->name,
                'note' => $request->note,
                'status' => 0
            ]);
        }

        PushNotificationAdminJob::dispatch(
            "User mới ",
            "Tên " . $request->name . " số " . $phone,
        );

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $userCreate
        ], 201);
    }
}
