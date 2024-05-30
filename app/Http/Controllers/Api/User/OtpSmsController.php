<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Http\Controllers\Controller;
use App\Models\HistorySms;
use App\Models\LastSentOtp;
use App\Models\MsgCode;
use App\Models\OtpCodePhone;
use App\Models\OtpConfig;
use App\Models\OtpUnit;
use App\Services\HistorySmsService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group  Gui OTP
 */
class OtpSmsController extends  Controller
{
    /**
     * Send
     */
    public function send(Request $request)   // send with api
    {

        $is_voice = true;

        $phone = $request->phone_number;

        $otp = Helper::generateRandomNum(6);
        $now = Helper::getTimeNowString();
        $phone = PhoneUtils::convert($phone);
        $otpExis = LastSentOtp::where('phone', $phone)->orderBy('id', 'desc')->first();
        $valid = PhoneUtils::check_valid($phone);

        if (Cache::lock($phone, 15)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã gửi",
            ], 400);
        }


        if (
            $phone == null ||
            $valid  == false
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],

            ], 400);
        }

        $hasLast = LastSentOtp::where("phone", $phone)->orderBy('id', 'desc')->first();

        $timeNow = Carbon::now('Asia/Ho_Chi_Minh');
        $dateFrom = $timeNow->year . '-' . $timeNow->month . '-' . $timeNow->day . ' 00:00:00';
        $dateTo = $timeNow->year . '-' . $timeNow->month . '-' . $timeNow->day . ' 23:59:59';


        $totalSMSInDay = LastSentOtp::where("phone", $phone)
            ->where('updated_at', '>=',  $dateFrom)
            ->where('updated_at', '<', $dateTo)
            ->get();


        if (count($totalSMSInDay) > 5) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => "TIME_IS_TOO_CLOSE",
                'msg' => "Hôm nay bạn đã gửi vượt quá 5 tin",
            ], 400);
        }

        if ($hasLast != null) {


            $time1 = Carbon::parse($hasLast->time_generate);
            $time1 = $time1->addSeconds(29);

            $time2 = Carbon::parse($now);


            $span =  $time2->diffInSeconds($time1, false);

            if ($span <= 29 && $span > 0) {


                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => "TIME_IS_TOO_CLOSE",
                    'msg' => "Vui lòng gửi lại sau " . (29 - (29 - $span)) . "s",
                ], 400);
            }
        }


        if ($valid == true) {
            // $noidung = "Ma xac thuc cua ban la: $otp Cam on ban da su dung dich vu.";

            $smsAPI = new SpeedSMSAPI("sourDP3FvL96maW7uk_7HbT-oDWsCu15");

            $phones = [$phone];
            $content = "[IKI TECH] Ma xac thuc cua ban tai DoApp la " . $otp;
            $type = 3;
            $sender = "IKI TECH";


            $response = $smsAPI->sendSMS($phones, $content, $type, $sender);
            //$response = $smsAPI->sendVoice($phones, $content);


            if ($response != null && isset($response["code"]) && $response["code"] == "00") {
                LastSentOtp::create([
                    "area_code" => "+84",
                    "otp" =>  $otp,
                    "ip" => $request->ip(),
                    "phone" => $phone,
                    "time_generate" => $now,
                ]);

                $otpExis = OtpCodePhone::where('phone', $phone)->first();

                if ($otpExis == null) {
                    OtpCodePhone::create([
                        "area_code" => "+84",
                        "otp" =>  $otp,
                        "phone" => $phone,
                        "time_generate" => $now,
                        "content" =>  $content,
                    ]);
                } else {
                    $otpExis->update([
                        "otp" =>  $otp,
                        "time_generate" => $now,
                    ]);
                }

                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            } else {
                return response()->json([
                    'code' => 300,
                    'success' => false,
                    'msg_code' => MsgCode::CANT_SEND_OTP[0],
                    'msg' => MsgCode::CANT_SEND_OTP[1],
                    'info' =>   $response
                ], 300);
            }
        }

        return response()->json([
            'code' => 300,
            'success' => false,
            'msg_code' => MsgCode::CANT_SEND_OTP[0],
            'msg' => MsgCode::CANT_SEND_OTP[1],
        ], 300);
    }
    public function send_v2(Request $request)   // send with api have store
    {
        $is_voice = true;
        $partner = null;
        $phone = $request->phone_number;
        $otp = Helper::generateRandomNum(6);
        $now = Helper::getTimeNowString();
        $phone = PhoneUtils::convert($phone);
        $otpExis = LastSentOtp::where('phone', $phone)->orderBy('id', 'desc')->first();
        $valid = PhoneUtils::check_valid($phone);

        if (Cache::lock($phone, 15)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã gửi",
            ], 400);
        }


        if (
            $phone == null ||
            $valid  == false
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],

            ], 400);
        }

        $hasLast = LastSentOtp::where("phone", $phone)->orderBy('id', 'desc')->first();

        $timeNow = Carbon::now('Asia/Ho_Chi_Minh');
        $dateFrom = $timeNow->year . '-' . $timeNow->month . '-' . $timeNow->day . ' 00:00:00';
        $dateTo = $timeNow->year . '-' . $timeNow->month . '-' . $timeNow->day . ' 23:59:59';


        $totalSMSInDay = LastSentOtp::where("phone", $phone)
            ->where('updated_at', '>=',  $dateFrom)
            ->where('updated_at', '<', $dateTo)
            ->get();


        if (count($totalSMSInDay) > 5) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => "TIME_IS_TOO_CLOSE",
                'msg' => "Hôm nay bạn đã gửi vượt quá 5 tin",
            ], 400);
        }

        if ($hasLast != null) {


            $time1 = Carbon::parse($hasLast->time_generate);
            $time1 = $time1->addSeconds(29);

            $time2 = Carbon::parse($now);


            $span =  $time2->diffInSeconds($time1, false);

            if ($span <= 29 && $span > 0) {


                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => "TIME_IS_TOO_CLOSE",
                    'msg' => "Vui lòng gửi lại sau " . (29 - (29 - $span)) . "s",
                ], 400);
            }
        }


        if ($valid == true) {
            // $noidung = "Ma xac thuc cua ban la: $otp Cam on ban da su dung dich vu.";

            $is_custom_send_otp = false;
            $otp_configs = OtpConfig::where('store_id', $request->store->id)->first();

            $otp_unit_used = OtpUnit::where('store_id', $request->store->id)
                ->where('is_use', true)->first();

            if (!empty($otp_configs)) {

                if ($otp_configs->is_use) {

                    $is_custom_send_otp = $otp_configs->is_use_from_default == false && $otp_configs->is_use_from_units == true && $otp_unit_used ? true : false;
                }
            }



            $smsAPI = new SpeedSMSAPI($is_custom_send_otp ? $otp_unit_used->token : "sourDP3FvL96maW7uk_7HbT-oDWsCu15");

            $phones = [$phone];

            $content_custom = "[IKI TECH] Ma xac thuc cua ban tai DoApp la ";
            if ($otp_unit_used) {

                $content_custom = str_replace("{otp}", $otp, $otp_unit_used->content);
                $partner = $otp_unit_used->partner;
            }

            $content = $is_custom_send_otp ? $content_custom : "[IKI TECH] Ma xac thuc cua ban tai DoApp la " . $otp;
            $type = 3;
            $sender =  $is_custom_send_otp ? $otp_unit_used->sender : "IKI TECH";


            $response = $smsAPI->sendSMS($phones, $content, $type, $sender);
            //$response = $smsAPI->sendVoice($phones, $content);


            if ($response != null && isset($response["code"]) && $response["code"] == "00") {
                LastSentOtp::create([
                    "area_code" => "+84",
                    "otp" =>  $otp,
                    "ip" => $request->ip(),
                    "phone" => $phone,
                    "time_generate" => $now,
                ]);

                // Add history sms
                HistorySmsService::addHistorySms($request->store->id, $phone, HistorySms::TYPE_AUTH, $content_custom, $partner);

                $otpExis = OtpCodePhone::where('phone', $phone)->first();

                if ($otpExis == null) {
                    OtpCodePhone::create([
                        "area_code" => "+84",
                        "otp" =>  $otp,
                        "phone" => $phone,
                        "time_generate" => $now,
                        "content" =>  $content,
                    ]);
                } else {
                    $otpExis->update([
                        "otp" =>  $otp,
                        "time_generate" => $now,
                    ]);
                }

                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            } else {
                return response()->json([
                    'code' => 300,
                    'success' => false,
                    'msg_code' => MsgCode::CANT_SEND_OTP[0],
                    'msg' => MsgCode::CANT_SEND_OTP[1],
                    'info' =>   $response
                ], 300);
            }
        }

        return response()->json([
            'code' => 300,
            'success' => false,
            'msg_code' => MsgCode::CANT_SEND_OTP[0],
            'msg' => MsgCode::CANT_SEND_OTP[1],
        ], 300);
    }
}



class SpeedSMSAPI
{
    const SMS_TYPE_QC = 1; // loai tin nhan quang cao
    const SMS_TYPE_CSKH = 2; // loai tin nhan cham soc khach hang
    const SMS_TYPE_BRANDNAME = 3; // loai tin nhan brand name cskh
    const SMS_TYPE_NOTIFY = 4; // sms gui bang brandname Notify
    const SMS_TYPE_GATEWAY = 5; // sms gui bang so di dong ca nhan tu app android, download app tai day: https://speedsms.vn/sms-gateway-service/

    private $ROOT_URL = "https://api.speedsms.vn/index.php";
    private $accessToken = "Your API access token";

    function __construct($api_key)
    {
        $this->accessToken = $api_key;
    }

    public function getUserInfo()
    {
        $url = $this->ROOT_URL . '/user/info';
        $headers = array('Accept: application/json');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, $this->accessToken . ':x');

        $results = curl_exec($ch);

        if (curl_errno($ch)) {
            return null;
        } else {
            curl_close($ch);
        }
        return json_decode($results, true);
    }

    public function sendSMS($to, $smsContent, $smsType, $sender)
    {
        if (!is_array($to) || empty($to) || empty($smsContent))
            return null;

        $type = SpeedSMSAPI::SMS_TYPE_CSKH;
        if (!empty($smsType))
            $type = $smsType;

        if ($type < 1 || $type > 8)
            return null;

        if (($type == 3 || $type == 5 || $type == 7 || $type == 8) && empty($sender))
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent, 'sms_type' => $type, 'sender' => $sender));

        $headers = array('Content-type: application/json');

        $url = $this->ROOT_URL . '/sms/send';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($http, CURLOPT_USERPWD, $this->accessToken . ':x');
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function sendMMS($to, $smsContent, $link, $sender)
    {
        if (!is_array($to) || empty($to) || empty($smsContent))
            return null;

        $type = SpeedSMSAPI::SMS_TYPE_CSKH;
        if (!empty($smsType))
            $type = $smsType;

        if ($type < 1 || $type > 8)
            return null;

        if (($type == 3 || $type == 5) && empty($sender))
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent, 'link' => $link, 'sender' => $sender));

        $headers = array('Content-type: application/json');

        $url = $this->ROOT_URL . '/mms/send';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($http, CURLOPT_USERPWD, $this->accessToken . ':x');
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function sendVoice($to, $smsContent)
    {
        if (empty($to) || empty($smsContent))
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent));

        $headers = array('Content-type: application/json');

        $url = $this->ROOT_URL . '/voice/otp';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($http, CURLOPT_USERPWD, $this->accessToken . ':x');
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }
}

class VnptSMSAPI
{
    private $ROOT_URL = "http://dongthap.vnpt.vn/sms/services/_index.asmx";

    public function sendSMS($req_id, $phone_number, $brandnameID, $templateID, $user, $password, $para_number, $paras)
    {
        if (empty($phone_number) || empty($brandnameID) || empty($templateID) ||  empty($user) || empty($password) || !is_array($paras)) {
            return null;
        }
        //Tạo chuỗi checkcode để xác thực thông tin
        $checkcode = md5($user . $phone_number . md5($password));

        //Tạo mảng các tham số cần truyền vào mẫu tin
        $params = [];
        foreach ($paras as $param) {
            $params[] = urldecode($param);
        }
        $params_string = implode(',', $params);

        // Tạo yêu cầu HTTP POST đến webservice
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ROOT_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'req_id' => $req_id,
            'sdt' => $req_id,
            'brandnameID' => $brandnameID,
            'templateID' => $templateID,
            'user' => $user,
            'checkcode' => $checkcode,
            'para_number' => $para_number,
            'paras' => $params_string,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Kiểm tra kết quả trả về từ webservice
        if ($response == 'SUCCESS') {
            // Tin nhắn đã được gửi thành công
            return null;
        } else {
            // Tin nhắn không được gửi thành công
            return false;
        }
    }
}
