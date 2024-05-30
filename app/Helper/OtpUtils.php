<?php

namespace App\Helper;

use App\Models\Customer;
use App\Models\OtpUnit;
use App\Services\Sms\SpeedSmsAPIService;

class OtpUtils
{
    static function otpHandle($request)
    {
        $customerPasserBy = Customer::where('store_id', $request->store->id)
            ->where('is_passersby', true)->first();

        if ($customerPasserBy == null) {
            $customerPasserBy = Customer::create(
                [
                    'area_code' => '+84',
                    'name' => "Khách vãng lai",
                    'name_str_filter' => StringUtils::convert_name_lowcase("Khách vãng lai"),
                    'phone_number' => "----------",
                    'email' => "",
                    'store_id' => $request->store->id,
                    'password' => bcrypt('DOAPP_BCRYPT_PASS'),
                    'official' => true,
                    "is_passersby" => true
                ]
            );
        }

        return  $customerPasserBy;
    }

    /**
     * Send Otp
     */
    static function sendOtp($otpUnit, $content, $phone, $type = null)
    {
        if ($otpUnit->partner == OtpUnit::SPEED_SMS) {
            $smsAPI = new SpeedSmsAPIService($otpUnit->token ?? "sourDP3FvL96maW7uk_7HbT-oDWsCu15");

            $type = 3; // type config default of speed sms

            $response = $smsAPI->sendSMS([$phone], $content, $type, $otpUnit->sender);
            if ($response != null && isset($response["code"]) && $response["code"] == 00) {
                return true; // Gửi sms thành công
            } else if ($response != null && isset($response["code"]) && $response["code"] != 200) {
                return $response['message'];
            }

            return "Fail sent sms"; // Gửi sms thất bại
        }
    }
}
