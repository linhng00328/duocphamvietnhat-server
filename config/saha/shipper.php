<?php

return [
    'list_shipper' => [
        0 => [
            'name' => 'Giao hàng tiết kiệm',
            'id' => 0,
            'fee_url' => 'https://services.giaohangtietkiem.vn/services/shipment/fee',
            'send_order_url' => 'https://services.giaohangtietkiem.vn/services/shipment/order/?ver=1.5',
            'check_token_url' => 'https://services.giaohangtietkiem.vn/services/shipment/fee',
            'get_info_and_history_order' => 'https://services.giaohangtietkiem.vn/services/shipment/v2/',
            'ship_speed' => true,
            'ship_speed_code_default' => "0",
            'image_url' => "https://i.imgur.com/JyXVvB0.png"
        ],
        1 => [
            'name' => 'Giao hàng nhanh',
            'id' => 1,
            'fee_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee',
            'send_order_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create',
            'check_token_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/available-services',
            'get_info_and_history_order' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail',
            'ship_speed' => true,
            'ship_speed_code_default' => "2",
            'image_url' => "https://i.imgur.com/5L3Cyag.png"
        ],
        2 => [
            'name' => 'Viettel Post',
            'id' => 2,
            'fee_url' => 'https://partner.viettelpost.vn/v2/order/getPrice',
            'check_token_url' => 'https://partner.viettelpost.vn/v2/user/listInventory',
            'send_order_url' => 'https://partner.viettelpost.vn/v2/order/createOrderNlp',
            'get_info_and_history_order' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail',
            'cancel_order' => 'https://partner.viettelpost.vn/v2/order/UpdateOrder',
            'ship_speed' => true,
            'ship_speed_code_default' => "PHS",
            'image_url' => "https://i.imgur.com/BDfNC0M.png"
        ],
        3 => [
            'name' => 'Vietnam Post',
            'id' => 3,
            'fee_url' => 'https://connect-my.vnpost.vn/customer-partner/ServicesCharge',
            'check_token_url' => 'https://connect-my.vnpost.vn/customer-partner/GetAccessToken',
            'send_order_url' => 'https://connect-my.vnpost.vn/customer-partner/CreateOrder',
            'get_info_and_history_order' => 'https://connect-my.vnpost.vn/customer-partner/getOrder',
            'cancel_order' => 'https://connect-my.vnpost.vn/customer-partner/orderCancel',
            'ship_speed' => true,
            'ship_speed_code_default' => "CTN009",
            'image_url' => "https://i.imgur.com/Ku7BDMX.png"
        ],
        4 => [
            'name' => 'Nhất Tín Logistics',
            'id' => 4,
            'fee_url' => 'https://apiws.ntlogistics.vn/v1/bill/calc-fee',
            'check_token_url' => 'https://apiws.ntlogistics.vn/v1/bill/calc-fee',
            'send_order_url' => 'https://apiws.ntlogistics.vn/v2/bill/create',
            'get_info_and_history_order' => 'https://apiws.ntlogistics.vn/v1/bill/tracking',
            'cancel_order' => 'https://apiws.ntlogistics.vn/v1/bill/destroy',
            'ship_speed' => true,
            'url_base' => 'https://apiws.ntlogistics.vn',
            'ship_speed_code_default' => "91",
            'image_url' => "https://i.imgur.com/FNn6HEo.png"
        ],
    ],
];
