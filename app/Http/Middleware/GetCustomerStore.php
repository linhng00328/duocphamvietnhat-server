<?php

namespace App\Http\Middleware;

use App\Models\MsgCode;
use App\Models\Store;
use Closure;

class GetCustomerStore
{
    public function handle($request, Closure $next)
    {

        $code = $request->route()->parameter('store_code') ?? null;
        $checkStoreExists = Store::where(
            'store_code',
            $code
        )->first();


        if (!empty($checkStoreExists)) {
            $platform = request()->header('platform') ?? null;
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
        return $next($request);
    }
}
