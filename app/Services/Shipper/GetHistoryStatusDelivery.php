<?php

namespace App\Services\Shipper;

use App\Helper\Place;
use App\Helper\StatusDefineCode;
use Exception;
use GuzzleHttp\Client;


class GetHistoryStatusDelivery
{

    public static function getInfoOrderGhtk($orderDB, $token, $code)
    {

        $config = config('saha.shipper.list_shipper')[0];
        $get_info_and_history_order = $config["get_info_and_history_order"];
        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->get(
                $get_info_and_history_order . $code,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->success == false) {
                return new Exception($jsonResponse->message);
            } else {
                // {
                //     "success": true,
                //     "message": "",
                //     "order": {
                //         "label_id": "S19328958.SGA8.P4.649884154",
                //         "partner_id": "a4",
                //         "order_id": null,
                //         "status": 2,
                //         "status_text": "Đã tiếp nhận",
                //         "created": "2022-01-14 13:30:25",
                //         "modified": "2022-01-14 13:30:26",
                //         "message": "Khối lượng tính cước tối đa: 1.00 kg",
                //         "pick_date": "2022-01-14",
                //         "deliver_date": "2022-01-15",
                //         "customer_fullname": "GHTK - HCM - Noi Thanh",
                //         "customer_tel": "0911222331",
                //         "address": "123 nguyễn chí thanh Phường Bến Nghé, Quận 1, TP Hồ Chí Minh",
                //         "storage_day": 0,
                //         "ship_money": 37000,
                //         "insurance": 15000,
                //         "value": 3000000,
                //         "weight": 400,
                //         "pick_money": 47000,
                //         "is_freeship": 1,
                //         "products": [
                //             {
                //                 "full_name": "bút",
                //                 "product_code": "65469367",
                //                 "weight": 0,
                //                 "cost": 0
                //             },
                //             {
                //                 "full_name": "tẩy",
                //                 "product_code": "65469368",
                //                 "weight": 0,
                //                 "cost": 0
                //             }
                //         ]
                //     }
                // }

                return $jsonResponse->order;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                return new Exception($jsonResponse->message);
            }
            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }

    public static function getInfoOrderGHN($orderDB, $token, $code)
    {
        $config = config('saha.shipper.list_shipper')[1];
        $get_info_and_history_order = $config["get_info_and_history_order"];
        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {

            $response = $client->post(
                $get_info_and_history_order . "?order_code=" . $code,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'json' => [
                        "order_code" => $code
                    ],
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->code != 200) {
                return new Exception($jsonResponse->message);
            } else {
                // {
                //     "code": 200,
                //     "message": "Success",
                //     "data": {
                //         "shop_id": 1600946,
                //         "client_id": 2728243,
                //         "return_name": "0868917689",
                //         "return_phone": "0868917689",
                //         "return_address": "183 bến vân đồn, quận 4",
                //         "return_ward_code": "20415",
                //         "return_district_id": 1446,
                //         "return_location": {
                //             "lat": 10.758837,
                //             "long": 106.711152,
                //             "cell_code": "AJKAEMJG",
                //             "place_id": "ChIJ4aWhaRMvdTERntDJfA3JApQ",
                //             "trust_level": 5,
                //             "wardcode": "20415"
                //         },
                //         "from_name": "0868917689",
                //         "from_phone": "0868917689",
                //         "from_address": "183 bến vân đồn, quận 4",
                //         "from_ward_code": "20415",
                //         "from_district_id": 1446,
                //         "from_location": {
                //             "lat": 10.758837,
                //             "long": 106.711152,
                //             "cell_code": "AJKAEMJG",
                //             "place_id": "ChIJ4aWhaRMvdTERntDJfA3JApQ",
                //             "trust_level": 5,
                //             "wardcode": "20415"
                //         },
                //         "deliver_station_id": 0,
                //         "to_name": "chinhbv11",
                //         "to_phone": "0868917688",
                //         "to_address": "Số 41",
                //         "to_ward_code": "20308",
                //         "to_district_id": 1444,
                //         "to_location": {
                //             "lat": 10.7883239,
                //             "long": 106.6888723,
                //             "cell_code": "AJJAEP8D",
                //             "place_id": "ChIJMXOaojIvdTERTHOiLNhGG2I",
                //             "trust_level": 5,
                //             "wardcode": "20308"
                //         },
                //         "weight": 500,
                //         "length": 20,
                //         "width": 20,
                //         "height": 20,
                //         "converted_weight": 1600,
                //         "image_ids": null,
                //         "service_type_id": 2,
                //         "service_id": 53320,
                //         "payment_type_id": 2,
                //         "payment_type_ids": [
                //             2
                //         ],
                //         "custom_service_fee": 0,
                //         "sort_code": "190-G-01-A8",
                //         "cod_amount": 820000,
                //         "cod_collect_date": null,
                //         "cod_transfer_date": null,
                //         "is_cod_transferred": false,
                //         "is_cod_collected": false,
                //         "insurance_value": 0,
                //         "order_value": 0,
                //         "pick_station_id": 0,
                //         "client_order_code": "",
                //         "required_note": "KHONGCHOXEMHANG",
                //         "content": "Serum dưỡng trắng NNO VITE Blister giúp dưỡng trắng sáng da &  đều màu da, ngăn ngừa sạm nám, cải thiện tình trạng lão hóa da - Hộp 3 vỉ x 10 viên [2422] [2 cái]",
                //         "note": "",
                //         "employee_note": "",
                //         "seal_code": "",
                //         "pickup_time": "2022-01-15T06:16:22.241Z",
                //         "items": [
                //             {
                //                 "name": "Serum dưỡng trắng NNO VITE Blister giúp dưỡng trắng sáng da &  đều màu da, ngăn ngừa sạm nám, cải thiện tình trạng lão hóa da - Hộp 3 vỉ x 10 viên",
                //                 "code": "2422",
                //                 "quantity": 2,
                //                 "category": {},
                //                 "weight": 1
                //             }
                //         ],
                //         "coupon": "",
                //         "_id": "61e266b6c419dc9e63547586",
                //         "order_code": "GAN6D8A9",
                //         "version_no": "db4b42f9-0dc3-492e-8eeb-b1b24d6caa3c",
                //         "updated_ip": "42.112.232.250",
                //         "updated_employee": 0,
                //         "updated_client": 2728243,
                //         "updated_source": "shiip",
                //         "updated_date": "2022-01-15T06:17:47.862Z",
                //         "updated_warehouse": 0,
                //         "created_ip": "42.112.232.250",
                //         "created_employee": 0,
                //         "created_client": 2728243,
                //         "created_source": "shiip",
                //         "created_date": "2022-01-15T06:16:22.17Z",
                //         "status": "ready_to_pick",
                //         "pick_warehouse_id": 1337,
                //         "deliver_warehouse_id": 20048000,
                //         "current_warehouse_id": 1337,
                //         "return_warehouse_id": 1337,
                //         "next_warehouse_id": 0,
                //         "current_transport_warehouse_id": 0,
                //         "leadtime": "2022-01-15T23:59:59Z",
                //         "order_date": "2022-01-15T06:16:22.241Z",
                //         "data": {
                //             "last_sort_code_print": "190-G-01-A8",
                //             "print_by_user_id": 2728243,
                //             "print_by_user_name": "Dr",
                //             "print_time": "2022-01-15T06:17:47.862Z"
                //         },
                //         "soc_id": "61e266b6c419dc9e63547585",
                //         "finish_date": null,
                //         "tag": [
                //             "truck"
                //         ],
                //         "is_partial_return": false
                //     }
                // }


                return  $jsonResponse->data;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                return new Exception($jsonResponse->message);
            }
            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }

    public static function getInfoOrderVietnamPost($orderDB, $token, $code)
    {
        $config = config('saha.shipper.list_shipper')[1];
        $get_info_and_history_order = $config["get_info_and_history_order"];
        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $get_info_and_history_order . "?order_code=" . $code,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'json' => [
                        "order_code" => $code
                    ],
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->code != 200) {
                return new Exception($jsonResponse->message);
            } else {
                // {
                //     "code": 200,
                //     "message": "Success",
                //     "data": {
                //         "shop_id": 1600946,
                //         "client_id": 2728243,
                //         "return_name": "0868917689",
                //         "return_phone": "0868917689",
                //         "return_address": "183 bến vân đồn, quận 4",
                //         "return_ward_code": "20415",
                //         "return_district_id": 1446,
                //         "return_location": {
                //             "lat": 10.758837,
                //             "long": 106.711152,
                //             "cell_code": "AJKAEMJG",
                //             "place_id": "ChIJ4aWhaRMvdTERntDJfA3JApQ",
                //             "trust_level": 5,
                //             "wardcode": "20415"
                //         },
                //         "from_name": "0868917689",
                //         "from_phone": "0868917689",
                //         "from_address": "183 bến vân đồn, quận 4",
                //         "from_ward_code": "20415",
                //         "from_district_id": 1446,
                //         "from_location": {
                //             "lat": 10.758837,
                //             "long": 106.711152,
                //             "cell_code": "AJKAEMJG",
                //             "place_id": "ChIJ4aWhaRMvdTERntDJfA3JApQ",
                //             "trust_level": 5,
                //             "wardcode": "20415"
                //         },
                //         "deliver_station_id": 0,
                //         "to_name": "chinhbv11",
                //         "to_phone": "0868917688",
                //         "to_address": "Số 41",
                //         "to_ward_code": "20308",
                //         "to_district_id": 1444,
                //         "to_location": {
                //             "lat": 10.7883239,
                //             "long": 106.6888723,
                //             "cell_code": "AJJAEP8D",
                //             "place_id": "ChIJMXOaojIvdTERTHOiLNhGG2I",
                //             "trust_level": 5,
                //             "wardcode": "20308"
                //         },
                //         "weight": 500,
                //         "length": 20,
                //         "width": 20,
                //         "height": 20,
                //         "converted_weight": 1600,
                //         "image_ids": null,
                //         "service_type_id": 2,
                //         "service_id": 53320,
                //         "payment_type_id": 2,
                //         "payment_type_ids": [
                //             2
                //         ],
                //         "custom_service_fee": 0,
                //         "sort_code": "190-G-01-A8",
                //         "cod_amount": 820000,
                //         "cod_collect_date": null,
                //         "cod_transfer_date": null,
                //         "is_cod_transferred": false,
                //         "is_cod_collected": false,
                //         "insurance_value": 0,
                //         "order_value": 0,
                //         "pick_station_id": 0,
                //         "client_order_code": "",
                //         "required_note": "KHONGCHOXEMHANG",
                //         "content": "Serum dưỡng trắng NNO VITE Blister giúp dưỡng trắng sáng da &  đều màu da, ngăn ngừa sạm nám, cải thiện tình trạng lão hóa da - Hộp 3 vỉ x 10 viên [2422] [2 cái]",
                //         "note": "",
                //         "employee_note": "",
                //         "seal_code": "",
                //         "pickup_time": "2022-01-15T06:16:22.241Z",
                //         "items": [
                //             {
                //                 "name": "Serum dưỡng trắng NNO VITE Blister giúp dưỡng trắng sáng da &  đều màu da, ngăn ngừa sạm nám, cải thiện tình trạng lão hóa da - Hộp 3 vỉ x 10 viên",
                //                 "code": "2422",
                //                 "quantity": 2,
                //                 "category": {},
                //                 "weight": 1
                //             }
                //         ],
                //         "coupon": "",
                //         "_id": "61e266b6c419dc9e63547586",
                //         "order_code": "GAN6D8A9",
                //         "version_no": "db4b42f9-0dc3-492e-8eeb-b1b24d6caa3c",
                //         "updated_ip": "42.112.232.250",
                //         "updated_employee": 0,
                //         "updated_client": 2728243,
                //         "updated_source": "shiip",
                //         "updated_date": "2022-01-15T06:17:47.862Z",
                //         "updated_warehouse": 0,
                //         "created_ip": "42.112.232.250",
                //         "created_employee": 0,
                //         "created_client": 2728243,
                //         "created_source": "shiip",
                //         "created_date": "2022-01-15T06:16:22.17Z",
                //         "status": "ready_to_pick",
                //         "pick_warehouse_id": 1337,
                //         "deliver_warehouse_id": 20048000,
                //         "current_warehouse_id": 1337,
                //         "return_warehouse_id": 1337,
                //         "next_warehouse_id": 0,
                //         "current_transport_warehouse_id": 0,
                //         "leadtime": "2022-01-15T23:59:59Z",
                //         "order_date": "2022-01-15T06:16:22.241Z",
                //         "data": {
                //             "last_sort_code_print": "190-G-01-A8",
                //             "print_by_user_id": 2728243,
                //             "print_by_user_name": "Dr",
                //             "print_time": "2022-01-15T06:17:47.862Z"
                //         },
                //         "soc_id": "61e266b6c419dc9e63547585",
                //         "finish_date": null,
                //         "tag": [
                //             "truck"
                //         ],
                //         "is_partial_return": false
                //     }
                // }


                return  $jsonResponse->data;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                return new Exception($jsonResponse->message);
            }
            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }

    public static function get_history_ghtk($orderDB, $token, $code)
    {

        $order = GetHistoryStatusDelivery::getInfoOrderGhtk($orderDB, $token, $code);
        if (!($order  instanceof Exception)) {
            return [
                'time' => $order->modified,
                'status_text' =>  $order->status_text,
                'ship_money' =>   $order->ship_money,
            ];
        } else {
            return $order;
        }
    }

    public static function get_history_ghn($orderDB, $token, $code)
    {

        $order = GetHistoryStatusDelivery::getInfoOrderGHN($orderDB, $token, $code);
        if (!($order  instanceof Exception)) {
            $arr_status = [
                "ready_to_pick"    =>
                "Đơn hàng vận chuyển vừa được tạo",
                "picking"    =>
                "Shipper đến lấy hàng",
                "cancel"    =>
                "Đơn hàng vận chuyển đã bị hủy",
                "money_collect_picking"    =>
                " Người giao hàng đang tương tác với người bán",
                "picked"    =>
                "Người giao hàng được chọn hàng",
                "storing"    =>
                "The goods has been shipped to GHN sorting hub",
                "transporting"    =>
                "Hàng đã được chuyển đến trung tâm phân loại GHN",
                "sorting"    =>
                "Hàng đang được phân loại (tại kho phân loại)",
                "delivering"    =>
                " Người giao hàng đang giao hàng cho khách hàng",
                "money_collect_delivering"    =>
                "Người giao hàng đang tương tác với người mua",
                "delivered"    =>
                "Hàng đã được giao cho khách hàng",
                "delivery_fail"    =>
                "Hàng hóa chưa được giao cho khách hàng",
                "waiting_to_return"    =>
                " Hàng đang chờ giao (có thể giao trong vòng 24 / 48h)",
                "return"    =>
                "Hàng đang chờ trả lại cho người bán / người bán sau 3 lần giao hàng không thành công",
                "return_transporting"    =>
                "Hàng đang được luân chuyển",
                "return_sorting"    =>
                "Hàng đang được phân loại (tại kho phân loại)",
                "returning"    =>
                "Người gửi hàng đang trả lại cho người bán",
                "return_fail"    =>
                "The returning is failed",
                "returned"    =>
                "Sự trở lại không thành công",
                "exception"    =>
                "Xử lý ngoại lệ hàng hóa (các trường hợp làm trái quy trình).",
                "damage"    =>
                "Hàng hóa bị hư hỏng",
                "lost"    =>
                "Hàng bị mất"
            ];

            return [
                'time' => $order->updated_date,
                'status_text' => $arr_status[$order->status] ?? "",
                // 'ship_money' =>  $jsonResponse->order->ship_money,
            ];
        } else {
            return $order;
        }
    }


    public static function status_from_delivery_to_local_statusGHTK($orderDB, $token, $code)
    {

        $order = GetHistoryStatusDelivery::getInfoOrderGhtk($orderDB, $token, $code);



        $arr_data_define_status_order = [
            -1 => StatusDefineCode::USER_CANCELLED,
            1 =>  StatusDefineCode::WAITING_FOR_PROGRESSING,
            2 =>  StatusDefineCode::SHIPPING,
            3 => StatusDefineCode::SHIPPING,
            4 =>  StatusDefineCode::SHIPPING,
            5 =>  StatusDefineCode::WAIT_FOR_PAYMENT,
            6 => StatusDefineCode::COMPLETED,
            7 => StatusDefineCode::DELIVERY_ERROR,
            8 => StatusDefineCode::DELIVERY_ERROR,
            9 => StatusDefineCode::DELIVERY_ERROR,
            10 => StatusDefineCode::DELIVERY_ERROR,
            11 =>  StatusDefineCode::CUSTOMER_HAS_RETURNS,
            12 =>  StatusDefineCode::SHIPPING,
            13 => StatusDefineCode::CUSTOMER_HAS_RETURNS,
            20 =>  StatusDefineCode::CUSTOMER_RETURNING,
            21 => StatusDefineCode::CUSTOMER_HAS_RETURNS,
            123 =>  StatusDefineCode::DELIVERY_ERROR,
            127 =>  StatusDefineCode::DELIVERY_ERROR,
            128 =>  StatusDefineCode::DELIVERY_ERROR,
            45 =>  StatusDefineCode::DELIVERY_ERROR,
            49 => StatusDefineCode::COMPLETED,
            410 =>  StatusDefineCode::SHIPPING,
        ];

        $arr_data_define_status_payment = [
            6 => StatusDefineCode::PAID,
        ];

        if (!($order  instanceof Exception)) {
            return [
                'payment_status' => $arr_data_define_status_payment[$order->status] ?? null,
                'order_status' =>   $arr_data_define_status_order[$order->status] ?? null,
            ];
        } else {
            return $order;
        }
    }

    public static function status_from_delivery_to_local_statusGHN($orderDB, $token, $code)
    {

        $order = GetHistoryStatusDelivery::getInfoOrderGHN($orderDB, $token, $code);


        $arr_data_define_status_order = [
            "ready_to_pick"    => StatusDefineCode::WAITING_FOR_PROGRESSING,
            "picking"    => StatusDefineCode::SHIPPING,
            "cancel"    => StatusDefineCode::USER_CANCELLED,
            "money_collect_picking"   => StatusDefineCode::SHIPPING,
            "picked"    => StatusDefineCode::SHIPPING,
            "storing"    => StatusDefineCode::SHIPPING,
            "transporting"    => StatusDefineCode::SHIPPING,
            "sorting"    => StatusDefineCode::SHIPPING,
            "delivering"  => StatusDefineCode::SHIPPING,
            "money_collect_delivering"   => StatusDefineCode::SHIPPING,
            "delivered"    => StatusDefineCode::COMPLETED,
            "delivery_fail"     => StatusDefineCode::DELIVERY_ERROR,
            "waiting_to_return"   => StatusDefineCode::SHIPPING,
            "return"    => StatusDefineCode::CUSTOMER_RETURNING,
            "return_transporting"   => StatusDefineCode::SHIPPING,
            "return_sorting"    => StatusDefineCode::SHIPPING,
            "returning"     => StatusDefineCode::SHIPPING,
            "return_fail"   => StatusDefineCode::SHIPPING,
            "returned"   => StatusDefineCode::CUSTOMER_CANCELLED,
            "exception"   => StatusDefineCode::DELIVERY_ERROR,
            "damage"    => StatusDefineCode::DELIVERY_ERROR,
            "lost"   => StatusDefineCode::DELIVERY_ERROR,
        ];

        $arr_data_define_status_payment = [
            "delivered"    => StatusDefineCode::PAID,
        ];

        if (!($order  instanceof Exception)) {
            return [
                'payment_status' => $arr_data_define_status_payment[$order->status] ?? null,
                'order_status' => $arr_data_define_status_order[$order->status] ?? null,
            ];
        } else {
            return $order;
        }
    }
}
// const WAITING_FOR_PROGRESSING = 0;
// const PACKING = 1;
// const OUT_OF_STOCK = 2;
// const USER_CANCELLED = 3;
// const CUSTOMER_CANCELLED = 4;
// const SHIPPING = 5;
// const DELIVERY_ERROR = 6;
// const CUSTOMER_RETURNING = 7;
// const CUSTOMER_HAS_RETURNS = 8;
// const WAIT_FOR_PAYMENT = 9;
// const COMPLETED = 10;

// const UNPAID = 0;
// const WAITING_FOR_PROGRESSING_PAYMENT = 1;
// const PAID = 2;
// const PARTIALLY_PAID = 3;
// const PAY_REFUNDS = 5;
