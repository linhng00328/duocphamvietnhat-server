<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Http\Controllers\Api\HandleReceiverSmsController;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use App\Models\OtpCodePhone;
use App\Models\SessionStaff;
use App\Models\SessionUser;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @group  User/Đăng nhập
 */
class LoginController extends Controller
{

    /**
     * Login
     * @bodyParam email_or_phone_number string required (Username, email hoặc số điện thoại)
     * @bodyParam password string required Password
     */
    public function login(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $email  = $request->phone_number;

        $dataCheckLogin = [
            'phone_number' => $phone,
            'password' => $request->password,
        ];


        if ($request->email_or_phone_number != null) {
            if (Helper::validEmail($request->email_or_phone_number) != null) {
                $email = $request->email_or_phone_number;
                $dataCheckLogin = [
                    'email' => $request->email_or_phone_number,
                    'password' => $request->password,
                ];
            } else {
                $phone = PhoneUtils::convert($request->email_or_phone_number);
                $dataCheckLogin = [
                    'phone_number' => PhoneUtils::convert($request->email_or_phone_number),
                    'password' => $request->password,
                ];
            }
        } else {
            $dataCheckLogin = [
                'phone_number' => $phone,
                'password' => $request->password,
            ];
        }


        //B1 xác thực tồn tại
        if (Auth::attempt($dataCheckLogin)) {
            $checkTokenExists = SessionUser::where(
                'user_id',
                Auth::id()
            )->first();
            //B2 tạo token
            if (empty($checkTokenExists)) {
                $userSession = SessionUser::create([
                    'token' => Str::random(40),
                    'refresh_token' => Str::random(40),
                    'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                    'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                    'user_id' => Auth()->id()
                ]);
            } else {
                $userSession =  $checkTokenExists;
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $userSession,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 200);
        }


        //Nếu không phải user thì là staff
        $checkStaffExists = null;

        if ($request->email_or_phone_number != null) {
            $checkStaffExists = Staff::where('username', $request->email_or_phone_number)
                ->first();
        }

        //B1 xác thực tồn tại
        if ($checkStaffExists != null && Hash::check($request->password, $checkStaffExists->password)) {

            $checkTokenExists = SessionStaff::where(
                'staff_id',
                $checkStaffExists->id
            )->first();


            //B2 tạo token
            if (empty($checkTokenExists)) {


                $userSession = SessionStaff::create([
                    'token' => Str::random(40),
                    'refresh_token' => Str::random(40),
                    'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                    'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                    'staff_id' => $checkStaffExists->id
                ]);
            } else {
                $userSession =  $checkTokenExists;
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $userSession,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ], 200);
        }


        return response()->json([
            'code' => 401,
            'success' => false,
            'msg_code' => MsgCode::WRONG_ACCOUNT_OR_PASSWORD[1],
            'msg' => MsgCode::WRONG_ACCOUNT_OR_PASSWORD[1],
        ], 401);
    }

    /**
     * Lấy lại mật khẩu
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam password string required Mật khẩu mới
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     * @bodyParam otp_from string  phone(từ sdt)  email(từ email) mặc định là phone
     */
    public function reset_password(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $email = $request->email;
        $otp = $request->otp;

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

        $user = User::where('phone_number', $phone)->first();


        $user = null;

        if ($request->email_or_phone_number != null) {
            if (Helper::validEmail($request->email_or_phone_number) != null) {
                $email = $request->email_or_phone_number;
                $user = User::where('email', $request->email_or_phone_number)->first();
            } else {
                $phone = PhoneUtils::convert($request->email_or_phone_number);
                $user = User::where('phone_number', $request->email_or_phone_number)->first();
            }
        } else {
            $user = User::where('phone_number', $phone)->first();
        }


        if ($user == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PHONE_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_PHONE_NUMBER_EXISTS[1],
            ], 400);
        }

        /////
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
        /////

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


        SessionUser::where('user_id',  $user->id)->delete();
        HandleReceiverSmsController::reset_otp($phone);

        $user->update(
            [
                'password' => bcrypt($request->password)
            ]
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Thay đổi mật khẩu
     * @bodyParam password string required Mật khẩu mới
     */
    public function change_password(Request $request)
    {
        $newPassword = $request->new_password;


        if ($request->staff == null) {
            $dataCheckLogin = [
                'phone_number' => $request->user->phone_number,
                'password' => $request->old_password,

            ];


            if (!Auth::attempt($dataCheckLogin)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OLD_PASSWORD[0],
                    'msg' => MsgCode::INVALID_OLD_PASSWORD[1],
                ], 400);
            }

            if (
                strlen($newPassword) < 6
            ) {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                    'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
                ], 400);
            }

            $user = $request->user;

            $user->update(
                [
                    'password' => bcrypt($newPassword)
                ]
            );

            SessionUser::where('user_id',  $user->id)->delete();
        } else {
            $checkStaffExists = Staff::where('id', $request->staff->id)
                ->first();

            if ($checkStaffExists != null && Hash::check($request->old_password, $checkStaffExists->password)) {
                $checkStaffExists->update(
                    [
                        'password' => bcrypt($newPassword)
                    ]
                );
            } else {

                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                    'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
                ], 400);
            }
        }




        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    /**
     * Kiểm tra email,phone_number đã tồn tại
     * Sẽ ưu tiên kiểm tra phone_number (kết quả true tồn tại, false không tồn tại)
     * @bodyParam phone_number required phone_number
     * @bodyParam email string required email
     */
    public function check_exists(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $email = $request->email;

        $list_check = [];
        $user = User::where('phone_number', $phone)->first();
        if ($user != null) {

            array_push($list_check, [
                "name" => "phone_number",
                "value" => true
            ]);
        } else {
            array_push($list_check, [
                "name" => "phone_number",
                "value" => false
            ]);
        }

        $user2 = User::where('email', $email)->first();
        if ($user2 != null) {


            array_push($list_check, [
                "name" => "email",
                "value" => true
            ]);
        } else {
            array_push($list_check, [
                "name" => "email",
                "value" => false
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $list_check
        ], 200);
    }
}
