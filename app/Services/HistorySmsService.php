<?php

namespace App\Services;

use App\Helper\Helper;
use App\Helper\OtpUtils;
use App\Models\HistorySms;
use App\Models\OtpUnit;
use Carbon\Carbon;

class HistorySmsService
{

    static function addHistorySms($storeId, $phone, $type, $content, $partner, $ip = null)
    {
        try {

            HistorySms::create([
                'store_id' => $storeId,
                'phone' => $phone,
                'type' => $type,
                'content' => $content,
                'partner' => $partner,
                'ip' => $ip,
                'time_generate' => Carbon::now()
            ]);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    static function sendOrderSms($storeId, $order)
    {
        try {
            $otpUnit = OtpUnit::where('is_order', true)->where('is_use', true)->where('store_id', $storeId)->first();

            if ($otpUnit != null && $otpUnit->sender != "IKI TECH") {
                $orderContent = $otpUnit->content_order;

                $orderContent = str_replace("{name}", $order->customer_name, $orderContent);
                $orderContent = str_replace("{order_code}", $order->order_code, $orderContent);
                $orderContent = str_replace("{total}", number_format($order->total_final, 0, '', '.') . 'đ', $orderContent);

                // Gửi otp
                $dataOtpSent = OtpUtils::sendOtp($otpUnit, $orderContent, $order->customer_phone);

                // Log lịch sử
                if ($dataOtpSent == true) {
                    HistorySmsService::addHistorySms($storeId, $order->customer_phone, HistorySms::TYPE_ORDER, $orderContent, $otpUnit->partner);
                }
                // else {
                //     HistorySmsService::addHistorySms($storeId, $order->customer_phone, HistorySms::TYPE_ORDER, $orderContent, $otpUnit->partner);
                // }
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
