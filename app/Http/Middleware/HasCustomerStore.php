<?php

namespace App\Http\Middleware;

use App\Helper\Helper;
use App\Models\MsgCode;
use App\Models\Store;
use Closure;

class HasCustomerStore
{
    public function handle($request, Closure $next)
    {

        $code = $request->route()->parameter('store_code') ?? null;
        $checkStoreExists = Store::where(
            'store_code',
            $code
        )->first();

        if (empty($checkStoreExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_STORE_CODE_EXISTS[0],
                'msg' => MsgCode::NO_STORE_CODE_EXISTS[1],
            ], 404);
        } else {

            //Block từ app
            if ($checkStoreExists->is_block_app && Helper::isMobile()) {
                return response()->json([
                    'code' => 403,
                    'success' => false,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Cửa hàng đã hết hạn sử dụng, \nvui lòng liên hệ chăm sóc khách hàng!",
                ], 403);
            }

            $platform = request()->header('platform');
            if ($platform  == "ios") {

                $storeFakeExists = Store::where(
                    'store_code',
                    $checkStoreExists->store_code_fake_for_ios
                )->first();

                if ($storeFakeExists  != null) {
                    $checkStoreExists =  $storeFakeExists;
                }
            }

            if ($platform  == "zalo_mini") {
                $storeFakeExists = Store::where(
                    'store_code',
                    $checkStoreExists->store_code_fake_for_zalo_mini
                )->first();

                if ($storeFakeExists  != null) {
                    $checkStoreExists =  $storeFakeExists;
                }
            }

            $request->merge([
                'store' => $checkStoreExists,
            ]);

            return $next($request);
        }
    }
}
