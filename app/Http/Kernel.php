<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \App\Http\Middleware\ClearTelescopeEntries::class,
        // \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        //\App\Http\Middleware\Cors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:600,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'admin_auth' => [\App\Http\Middleware\AdminLogin::class], //buộc đăng nhập admin
        'user_auth' => [\App\Http\Middleware\UserLogin::class], //buộc đăng nhập user
        'customer_auth' => [\App\Http\Middleware\CustomerLogin::class], //buộc đăng nhập customer
        'get_customer_auth' => [\App\Http\Middleware\GetCustomerLogin::class], //lấy thông tin customer next
        'get_customer_auth_cart' => [\App\Http\Middleware\GetCustomerLoginCart::class], //lấy thông tin customer next
        'has_store' => [\App\Http\Middleware\HasStore::class], //buộc kiểm tra tồn tại store
        'check_staff' => [\App\Http\Middleware\CheckStaff::class], //buộc phải là nhân viên
        'is_sale_staff' => [\App\Http\Middleware\IsStaffSale::class], //Kiêm tra phải api sale không
        'has_product' => [\App\Http\Middleware\HasProduct::class], //buộc kiểm tra tồn tại product
        'has_branch' => [\App\Http\Middleware\HasBranch::class], //buộc kiểm tra tồn tại chi nhánh
        'has_customer_store' => [\App\Http\Middleware\HasCustomerStore::class], //buộc kiểm tra tồn tại store code
        'get_customer_store' => [\App\Http\Middleware\GetCustomerStore::class], //store code có thể null
        'has_order' => [\App\Http\Middleware\HasOrder::class], //buộc kiểm tra tồn tại order
        'check_order_paid' => [\App\Http\Middleware\CheckOrderPaid::class], //kiểm tra order đã thanh toán chưa
        'timezone' => [\App\Http\Middleware\TimeZoneMiddleware::class],
        'record_access' => [\App\Http\Middleware\RecordMiddleware::class],
        'up_speed' => [\App\Http\Middleware\UpSpeed::class],
        'handle_price_agency' => [\App\Http\Middleware\HandlePriceAgency::class],


    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        // 'timezone' => \App\Http\Middleware\TimeZoneMiddleware::class,
    ];
}
