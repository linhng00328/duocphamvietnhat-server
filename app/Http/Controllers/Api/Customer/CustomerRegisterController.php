<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Helper\StringUtils;
use App\Http\Controllers\Api\HandleReceiverSmsController;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\Customer;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use App\Models\OtpCodePhone;
use App\Models\PointSetting;
use App\Models\ReferralPhoneCustomer;
use App\Helper\PointCustomerUtils;
use App\Helper\SendToWebHookUtils;
use App\Helper\TypeFCM;
use App\Jobs\PushNotificationStaffJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\OtpConfig;
use App\Models\OtpUnit;
use App\Models\Staff;
use Illuminate\Http\Request;

/**
 * @group  Customer/Đăng ký
 */
class CustomerRegisterController extends Controller
{

    /**
     * Register
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam email string required Email
     * @bodyParam password string required Password
     * @bodyParam name string required Họ và tên
     * @bodyParam sex int required (0 ko xác định - 1 nam - 2 nữ)
     * @bodyParam referral_phone_number string số điện thoại giới thiệu
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     * @bodyParam otp_from string  phone(từ sdt)  email(từ email) mặc định là phone
     * @bodyParam from string  minizalo (bo qua otp)
     * 
     */
    public function register(Request $request)
    {

        $phone = PhoneUtils::convert($request->phone_number);
        $otp = $request->otp;
        $email = $request->email;


        $referral_phone_number = $request->referral_phone_number;
        $is_valid = false;
        if ($referral_phone_number) {
            $referral_phone_number = PhoneUtils::convert($request->referral_phone_number);

            $is_valid = PhoneUtils::check_valid($referral_phone_number);

            // if ($is_valid == false) {

            //     return response()->json([
            //         'code' => 400,
            //         'success' => false,
            //         'msg_code' => MsgCode::INVALID_REFERRAL_PHONE_NUMBER[0],
            //         'msg' => MsgCode::INVALID_REFERRAL_PHONE_NUMBER[1],
            //     ], 400);
            // }

            if ($referral_phone_number == $phone) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => 'Không thể  nhập SĐT giới thiệu của bạn',
                ], 400);
            }
        }


        if (
            $referral_phone_number != null
        ) {

            if (!Customer::where('phone_number', $referral_phone_number)
                ->where('store_id', $request->store->id)
                ->where('official', true)
                ->exists()) {

                // return response()->json([
                //     'code' => 400,
                //     'success' => false,
                //     'msg_code' => MsgCode::INVALID_REFERRAL_PHONE_NUMBER[0],
                //     'msg' => MsgCode::INVALID_REFERRAL_PHONE_NUMBER[1],
                // ], 400);
            }
        }

        if (Customer::where('phone_number', $phone)
            ->where('store_id', $request->store->id)
            ->where('official', true)
            ->exists()
        ) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ], 409);
        }


        if (
            $request->email != null &&
            strlen($request->email) > 0 &&
            Customer::where('email', $request->email)->where('store_id', $request->store->id)
            ->where('official', true)
            ->exists()

        ) {
            return response()->json([
                'code' => 409,
                'success' => false,
                'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS[0],
                'msg' => MsgCode::EMAIL_ALREADY_EXISTS[1],
            ], 409);
        }

        //Config OTP
        $is_use_otp = true;
        $otp_configs = OtpConfig::where('store_id', $request->store->id)
            ->first();
        $otp_unit_used = OtpUnit::where('store_id', $request->store->id)
            ->where('is_use', true)->first();
        if (!empty($otp_configs)) {

            if ($otp_configs->is_use) {

                $is_use_otp = $otp_configs->is_use_from_default == true || ($otp_configs->is_use_from_units == true && $otp_unit_used != null)  ? true : false;
            } else {

                $is_use_otp = false;
            }
        }

        $from = "";
        $type = "";
        if ($request->from != "minizalo") {

            if ($request->otp_from == "email") {

                if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_EMAIL[0],
                        'msg' => MsgCode::INVALID_EMAIL[1],
                    ], 400);
                }


                $from = $request->email;
                $type = "email";
                $otpExis = OtpCodeEmail::where('email', $request->email)
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
                if ($otpExis == null && $is_use_otp === true) {
                    return response()->json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_OTP[0],
                        'msg' => MsgCode::INVALID_OTP[1],
                    ], 400);
                }
            }
        }


        if (HandleReceiverSmsController::has_expired_otp($from, $type) && $is_use_otp === true) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXPIRED_PIN_CODE[0],
                'msg' => MsgCode::EXPIRED_PIN_CODE[1],
            ], 400);
        }

        PushNotificationAdminJob::dispatch(
            "Store " . $request->store->name . " của user " . $request->store->user->name,
            "Có customer mới " . $request->name . " " . $request->phone_number,
        );

        $customerCreate = null;
        if ($request->otp_from == "email") {
            $customerCreate  =   Customer::where('email', $request->email)->where('store_id', $request->store->id)->first();
        } else {
            $customerCreate  =   Customer::where('phone_number', $phone)->where('store_id', $request->store->id)->first();
        }

        if ($customerCreate  != null) {
            $customerCreate->update(
                [
                    'area_code' => '+84',
                    'phone_number' => $phone,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'name' => $request->name,
                    'store_id' => $request->store->id,
                    'sex' => $request->sex,
                    'official' => true,
                    'referral_phone_number' => $is_valid ? $referral_phone_number : null
                ]
            );
        } else {

            $customerHasPhoneNoEmail  =   Customer::where('phone_number', $phone)->where('store_id', $request->store->id)->first();
            if ($customerHasPhoneNoEmail != null) {
                $customerHasPhoneNoEmail->update(
                    [
                        'area_code' => '+84',
                        'phone_number' => $phone,
                        'email' => $request->email,
                        'password' => bcrypt($request->password),
                        'name' => $request->name,
                        'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                        'store_id' => $request->store->id,
                        'sex' => $request->sex,
                        'official' => true,
                        'referral_phone_number' => $is_valid ? $referral_phone_number : null
                    ]
                );
            } else {
                $customerCreate = Customer::create(
                    [
                        'area_code' => '+84',
                        'phone_number' => $phone,
                        'email' => $request->email,
                        'password' => bcrypt($request->password),
                        'name' => $request->name,
                        'name_str_filter' => StringUtils::convert_name_lowcase($request->name),
                        'store_id' => $request->store->id,
                        'official' => true,
                        'sex' => $request->sex,
                        'referral_phone_number' => $is_valid ? $referral_phone_number : null
                    ]
                );
            }
        }

        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        //Tặng điểm khi đăng ký lần đầu
        if ($pointSetting !== null) {

            if ($pointSetting->point_register_customer > 0) {

                PointCustomerUtils::add_sub_point(
                    PointCustomerUtils::REGISTER_CUSTOMER,
                    $request->store->id,
                    $customerCreate->id,
                    $pointSetting->point_register_customer,
                    $customerCreate->id,
                    $request->phone_number
                );
            }
        }

        //nếu có sdt giới thiệu
        if ($referral_phone_number != null && $is_valid) {

            $customerRefer = Customer::where('phone_number', $referral_phone_number)
                ->where('store_id', $request->store->id)->first();

            if ($customerRefer  != null) {
                ReferralPhoneCustomer::create([
                    'store_id' => $request->store->id,
                    "customer_id" =>   $customerRefer->id,
                    "introduce_customer_id" =>  $customerCreate->id,
                    "introduce_customer_phone" => $customerCreate->phone_number,
                ]);

                if ($customerRefer->sale_staff_id) {
                    $sale_exists = Staff::where('id', $customerRefer->sale_staff_id)
                        ->where('store_id', $request->store->id)
                        ->where('is_sale', true)
                        ->exists();

                    if ($sale_exists) {
                        $now = Helper::getTimeNowDateTime();
                        $customerCreate->update(
                            [
                                'sale_staff_id' => $customerRefer->sale_staff_id,
                                'time_sale_staff' => $now
                            ]
                        );
                        PushNotificationStaffJob::dispatch(
                            $request->store->id,
                            'Khách hàng của sale',
                            $customerCreate->name . ' đã trở thành khách hàng của bạn(' . $customerRefer->name . ' giới thiệu)',
                            TypeFCM::NEW_CUSTOMER_SALE,
                            $customerCreate->id,
                            null,
                            $customerRefer->sale_staff_id
                        );
                    }
                }

                //tính điểm cho customer
                if ($pointSetting != null) {
                    if ($pointSetting->point_introduce_customer) {
                        PointCustomerUtils::add_sub_point(
                            PointCustomerUtils::REFERRAL_CUSTOMER,
                            $request->store->id,
                            $customerRefer->id,
                            $pointSetting->point_introduce_customer,
                            $customerCreate->id,
                            $request->phone_number
                        );
                    }
                }
            }
        }

        SendToWebHookUtils::sendToWebHook($request, SendToWebHookUtils::NEW_CUSTOMER,   $customerCreate);

        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $customerCreate
        ], 201);
    }
}
