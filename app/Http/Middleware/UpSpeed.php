<?php

namespace App\Http\Middleware;

use Closure;

class UpSpeed
{

    const SPEED_HOME_APP_CUSTOMER = "SPEED_HOME_APP_CUSTOMER";
    const SPEED_PRODUCTS_CUSTOMER = "SPEED_PRODUCTS_CUSTOMER";
    const SPEED_IMAGE_ONE_PRODUCT_CUSTOMER = "SPEED_IMAGE_ONE_PRODUCT_CUSTOMER";
    const SPEED_IMAGE_ONE_POST_CUSTOMER = "SPEED_IMAGE_ONE_POST_CUSTOMER";
    const SPEED_BANNER_IOS_APP_CUSTOMER = "SPEED_BANNER_IOS_APP_CUSTOMER";
    const SPEED_HOME_CUSTOMER_PRODUCT_BY_CATEGORY = "SPEED_HOME_CUSTOMER_PRODUCT_BY_CATEGORY";

    public function handle($request, Closure $next)
    {

        $url = $request->fullUrl();
        $ios = request()->header('platform');

        if ((str_contains($url, '/home_app') || str_contains($url, '/home_web')) && str_contains($url, '/customer/')) {
            $request->merge([
                'up_speed' => UpSpeed::SPEED_HOME_APP_CUSTOMER,
            ]);
        }
        if ((str_contains($url, '/home_app') || str_contains($url, '/product_by_category')) && str_contains($url, '/customer/')) {
            $request->merge([
                'up_speed' => UpSpeed::SPEED_HOME_CUSTOMER_PRODUCT_BY_CATEGORY,
            ]);
        }
        if ((str_contains($url, '/products')
        ) && str_contains($url, '/customer/')) {
            $request->merge([
                'up_speed' => UpSpeed::SPEED_PRODUCTS_CUSTOMER,
            ]);
        }

        if ((str_contains($url, '/products/')
        ) && str_contains($url, '/customer/')) {
            $request->merge([
                'up_speed_image' => UpSpeed::SPEED_IMAGE_ONE_PRODUCT_CUSTOMER,
            ]);
        }

        if ((str_contains($url, '/posts/')
        ) && str_contains($url, '/customer/')) {
            $request->merge([
                'up_speed_image' => UpSpeed::SPEED_IMAGE_ONE_POST_CUSTOMER,
            ]);
        }

        if ($ios == 'ios') {
            $request->merge([
                'up_speed_banner_ios' => UpSpeed::SPEED_BANNER_IOS_APP_CUSTOMER,
            ]);
        }

        return $next($request);
    }
}
