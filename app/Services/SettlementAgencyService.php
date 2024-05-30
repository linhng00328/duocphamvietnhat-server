<?php

namespace App\Services;

use App\Helper\TypeFCM;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\Agency;
use App\Models\AgencyConfig;
use App\Models\PayAgency;
use App\Models\Store;
use App\Models\UserDeviceToken;
use App\Services\BalanceCustomerService;


class SettlementAgencyService
{
    public static function settlement($store_id)
    {
        $store = Store::where('id', $store_id)->first();

        $agencys = Agency::where('store_id', $store_id)
            ->get();

        

        $configExists = AgencyConfig::where(
            'store_id',
            $store_id
        )->first();

        $length_settlement = 0;
        foreach ($agencys as $agency) {

            if ($agency == null) {
                continue;
            }


            if ($configExists  == null || $configExists->payment_limit == null) {
                continue;
            }

            if ($agency->balance < $configExists->payment_limit) {
                continue;
            }

            $payAfter = PayAgency::where('store_id', $store_id)
                ->where('agency_id',  $agency->id)->where('status', 0)->first();
            if(!$payAfter) {
                continue;
            }
            // Cập nhật lại trạng thái sau khi đã quyết toán cho toàn bộ đại lý
            $payAfter->update(['status' => 2]);
            BalanceCustomerService::change_balance_agency(
                $store_id,
                $agency->customer_id,
                BalanceCustomerService::PAYMENT_REQUEST,
                -$payAfter->money,
                $payAfter->id,
                -$payAfter->money
            );

            if ($payAfter  != null) {
                continue;
            }


            PushNotificationCustomerJob::dispatch(
                $store_id,
                $agency->customer_id,
                "Shop " . $store->name,
                "Đã gửi yêu cầu quyết toán số dư đại lý cho bạn",
                TypeFCM::NEW_PERIODIC_SETTLEMENT,
                null
            );

            $length_settlement++;

            // PayAgency::create([
            //     "store_id" => $store_id,
            //     "agency_id"  =>  $agency->id,
            //     "money"  =>  $agency->balance,
            //     "status"  => 0,
            //     "from"  => 1,
            // ]);
        }

        $deviceTokens = UserDeviceToken::where('user_id', $store->user_id)
            ->pluck('device_token')
            ->toArray();

        if ($length_settlement > 0) {
            PushNotificationUserJob::dispatch(
                $store->id,
                $store->user_id,
                'Shop ' . $store->name,
                'Đã lên danh sách quyết toán cho đại lý',
                TypeFCM::NEW_PERIODIC_SETTLEMENT,
                null,
                null
            );
        }
    }
}
