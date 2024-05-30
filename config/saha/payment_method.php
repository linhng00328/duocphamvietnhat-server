<?php

use App\Helper\RevenueExpenditureUtils;

return [

    'payment_method' => [
        0 => [
            'name' => 'Thanh toán khi nhận hàng',
            'description' => null,
            'id' => 0,
            'field' => ['description'],
            'define_field' => ['Mô tả'],
            'is_auto' => false,
            'payment_method_id' => RevenueExpenditureUtils::PAYMENT_TYPE_COD
        ],
        1 => [
            'name' => 'Chuyển khoản đến tài khoản ngân hàng',
            'description' => "Chuyển khoản thủ công đến tài khoản ngân hàng của shop",
            'id' => 1,
            'field' => ['payment_guide'],
            'define_field' => ['Hướng dẫn thanh toán'],
            'is_auto' => false,
            'payment_method_id' => RevenueExpenditureUtils::PAYMENT_TYPE_TRANSFER
        ],
        2 => [
            'name' => 'Thanh toán bằng VNPay',
            'description' => 'Thanh toán bằng VNPay hỗ trợ thanh toán bằng Internet Banking, thẻ ATM ngân hàng và QRCode',
            'id' => 2,
            'field' => ['vnp_TmnCode', 'vnp_HashSecret'],
            'define_field' => ['Terminal ID/Mã Website', 'Secret Key/Chuỗi bí mật tạo checksum'],
            'is_auto' => true,
            'payment_method_id' => RevenueExpenditureUtils::PAYMENT_TYPE_ELECTRONIC_WALLET
        ],
        3 => [
            'name' => 'Thanh toán bằng OnePay',
            'description' => 'Thanh toán bằng OnePay hỗ trợ thanh toán bằng Internet Banking, thẻ ATM ngân hàng và QRCode',
            'id' => 3,
            'field' => ['merchant', 'hascode', 'access_code'],
            'define_field' => ['Merchant', 'Hascode', 'AccessCode'],
            'is_auto' => true,
            'payment_method_id' => RevenueExpenditureUtils::PAYMENT_TYPE_ELECTRONIC_WALLET
        ],
        4 => [
            'name' => 'Thanh toán bằng Momo',
            'description' => 'Thanh toán bằng Momo',
            'id' => 4,
            'field' => ['partnerCode', 'accessKey', 'serectKey'],
            'define_field' => ['partnerCode', 'accessKey', 'serectKey'],
            'is_auto' => true,
            'payment_method_id' => RevenueExpenditureUtils::PAYMENT_TYPE_ELECTRONIC_WALLET
        ],
        // 3 => [
        //     'name' => 'Thanh toán bằng ví momo',
        //     'description' => 'Thanh toán bằng ví MOMO hỗ trợ thanh toán bằng internetbanking và QRCode',
        //     'id' => 3,
        //     'field' => ['asset_key', 'hide_key'],
        //     'define_field' => ['Mã asset', 'Chìa khóa ẩn'],
        //     'is_auto' => false
        // ],

    ],
];
