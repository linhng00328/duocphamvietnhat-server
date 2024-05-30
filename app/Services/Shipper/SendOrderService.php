<?php

namespace App\Services\Shipper;

use App\Helper\Place;
use App\Helper\StatusDefineCode;
use App\Models\OrderShiperCode;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Services\Shipper\GHN\GHNUtils;
use App\Services\Shipper\NhattinPost\NhattinPostUtils;
use App\Services\Shipper\VietnamPost\VietnamPostUtils;
use App\Services\Shipper\ViettelPost\ViettelPostUtils;
use Exception;
use GuzzleHttp\Client;


class SendOrderService
{

    public static function send_order_ghtk($orderDB, $addressPickupExists, $token)
    {

        $config = config('saha.shipper.list_shipper')[0];
        $send_order_url = $config["send_order_url"];

        //Dữ liệu up
        $products = array();
        foreach ($orderDB->line_items as $line_item) {
            array_push(
                $products,
                [
                    "name" => $line_item->product->name,
                    "price" => $line_item->item_price,
                    "weight" => ($line_item->product->weight ?? 100) / 1000,
                    "quantity" => $line_item->quantity,
                    "product_code" => $line_item->product->id
                ]

            );
        }

        $order = [
            "id" => $orderDB->order_code,
            "pick_name" => $addressPickupExists->name,
            "pick_address" => $addressPickupExists->address_detail,

            "pick_tel" => $addressPickupExists->phone,

            "pick_province" => Place::getNameProvince($addressPickupExists->province),
            "pick_district" => Place::getNameDistrict($addressPickupExists->district),
            "pick_ward" => Place::getNameWards($addressPickupExists->wards),
            "transport" => "road",
            "tel" => $orderDB->customer_phone,
            "name" => $orderDB->customer_name,
            "address" => $orderDB->customer_address_detail,
            "province" => Place::getNameProvince($orderDB->customer_province),
            "district" => Place::getNameDistrict($orderDB->customer_district),
            "ward" => Place::getNameWards($orderDB->customer_wards),
            "pick_money" => (int)  $orderDB->cod,
            "value" => (int) ($orderDB->total_before_discount),
            "hamlet" => "Khác",

            'total_weight' => ($orderDB->package_weight ?? 100) / 1000,
            "is_freeship" => $orderDB->ship_discount_amount > 0 || ($orderDB->remaining_amount == 0 && $orderDB->cod == 0) ? "1" : "0",
            // "pick_date"=> "2016-09-30"
        ];

        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => [
                        'order' => $order,
                        'products' => $products,
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->success == false) {
                return new Exception($jsonResponse->message);
            } else {
                //   {#1640
                //   +"success": true
                //   +"message": "Các đơn hàng đã được add vào hệ thống GHTK thành công. Thông tin đơn hàng thành công được trả về trong trường success_orders."
                //   +"order": {#1638
                //     +"partner_id": "1812211JLY75H4"
                //     +"label": "S19328958.HN11.VP11B.651117667"
                //     +"area": 1
                //     +"fee": 22000
                //     +"insurance_fee": 0
                //     +"estimated_pick_time": "Chiều 2022-01-14"
                //     +"estimated_deliver_time": "Sáng 2022-01-15"
                //     +"products": []
                //     +"status_id": 2
                //     +"tracking_id": 651117667
                //     +"sorting_code": "HN11.VP11B"
                //     +"is_xfast": 0
                //   }
                //   +"warning_message": ""
                // }

                return [
                    'code' => $jsonResponse->order->label,
                    'fee' => $jsonResponse->order->fee,
                ];
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

    public static function send_order_ghn($orderDB, $addressPickupExists, $token)
    {
        $config = config('saha.shipper.list_shipper')[1];
        $send_order_url = $config["send_order_url"];

        //Dữ liệu up
        $items = array();
        foreach ($orderDB->line_items as $line_item) {
            array_push(
                $items,
                [
                    "name" => $line_item->product->name,
                    "price" => $line_item->item_price,
                    "weight" => $line_item->product->weight ?? 100,
                    "quantity" => $line_item->quantity,
                    "code" => (string)$line_item->product->id,
                ]

            );
        }

        $province_name = Place::getNameProvince($orderDB->customer_province);
        $district_name = Place::getNameDistrict($orderDB->customer_district);
        $wards_name = Place::getNameWards($orderDB->customer_wards);


        $provinceIdTo = GHNUtils::getIDProvinceGHN($province_name);
        $districtIdTo = GHNUtils::getIDDistrictGHN($provinceIdTo,  $district_name);

        $wardCodeTo = GHNUtils::getWardCodeGHN($token, $districtIdTo,   $wards_name);

        $orderData = [

            "payment_type_id" => $orderDB->ship_discount_amount > 0  || ($orderDB->remaining_amount == 0 && $orderDB->cod == 0) ? 1 : 2,
            "required_note" => "KHONGCHOXEMHANG",
            "to_name" => $orderDB->customer_name,
            "to_phone" => $orderDB->customer_phone,
            "to_address" => $orderDB->customer_address_detail,
            "to_ward_code" => $wardCodeTo,
            "to_district_id" =>  $districtIdTo,
            "cod_amount" => $orderDB->cod,
            'weight' => $orderDB->package_weight ?? 100,
            "length" => $orderDB->package_length ?? 100,
            "width" => $orderDB->package_width ?? 100,
            "height" => $orderDB->package_height ?? 100,
            "service_id" => 0,
            "service_type_id" => 2,
            "items" => $items
        ];

        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' =>  $orderData
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            if ($jsonResponse->code != 200) {
                return new Exception($jsonResponse->code_message_value);
            } else {
                // {#1635
                //     +"code": 200
                //     +"code_message_value": "Do diễn biến phức tạp của dịch Covid-19, thời gian giao hàng có thể dài hơn dự kiến từ 1-5 ngày."
                //     +"data": {#1629
                //       +"order_code": "GAN66XDB"
                //       +"sort_code": "190-G-01-A8"
                //       +"trans_type": "truck"
                //       +"ward_encode": ""
                //       +"district_encode": ""
                //       +"fee": {#1615
                //         +"main_service": 22000
                //         +"insurance": 0
                //         +"station_do": 0
                //         +"station_pu": 0
                //         +"return": 0
                //         +"r2s": 0
                //         +"coupon": 0
                //       }
                //       +"total_fee": 22000
                //       +"expected_delivery_time": "2022-01-15T23:59:59Z"
                //     }
                //     +"message": "Success"
                //     +"message_display": "Tạo đơn hàng thành công. Mã đơn hàng: GAN66XDB"
                //   }

                return [
                    'code' => $jsonResponse->data->order_code,
                    'fee' => $jsonResponse->data->total_fee,
                ];
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

    public static function send_order_vtp($orderDB, $addressPickupExists, $token)
    {
        $config = config('saha.shipper.list_shipper')[2];
        $send_order_url = $config["send_order_url"];
        $nameProducts = null; // Thông tin đơn hàng (áo, quần..)

        //Dữ liệu up
        $LIST_ITEM = array();
        foreach ($orderDB->line_items as $line_item) {
            $distribute = json_decode($line_item->distributes);
            $nameProducts .= $line_item->product->name;
            try {
                if (isset($distribute[0])) {
                    if (isset($distribute[0]->value) && !empty($distribute[0]->value)) {
                        $nameProducts .= ":" . $distribute[0]->value;
                    }

                    if (isset($distribute[0]->sub_element_distributes) && !empty($distribute[0]->sub_element_distributes)) {
                        $nameProducts .= ":" . $distribute[0]->sub_element_distributes;
                    }
                }
            } catch (\Throwable $th) {
            }
            $nameProducts .= " x " . $line_item->quantity . ", ";
            array_push(
                $LIST_ITEM,
                [
                    "PRODUCT_NAME" => $line_item->product->name,
                    "PRODUCT_PRICE" => $line_item->item_price,
                    "PRODUCT_WEIGHT" => ($line_item->product->weight <= 0 ? 100 : $line_item->product->weight),
                    "PRODUCT_QUANTITY" => $line_item->quantity,
                ]

            );
        }
        if ($nameProducts != null) {
            $nameProducts .= "...";
        }


        $RECEIVER_PROVINCE = ViettelPostUtils::getIDProvinceViettelPost($orderDB->customer_province_name);

        $RECEIVER_DISTRICT = ViettelPostUtils::getIDDistrictViettelPost($RECEIVER_PROVINCE, $orderDB->customer_district_name);
        $RECEIVER_WARD = ViettelPostUtils::getIDWardViettelPost($RECEIVER_DISTRICT, $orderDB->customer_wards_name);

        $RECEIVER_ADDRESS = "$orderDB->customer_address_detail, $orderDB->customer_wards_name, $orderDB->customer_district_name, $orderDB->customer_province_name";

        $SENDER_PROVINCE = 1;
        $SENDER_DISTRICT = 1;
        $SENDER_WARD = 1;



        if ($addressPickupExists != null) {
            $SENDER_PROVINCE = ViettelPostUtils::getIDProvinceViettelPost($addressPickupExists->province_name);
            $SENDER_DISTRICT = ViettelPostUtils::getIDDistrictViettelPost($SENDER_PROVINCE, $addressPickupExists->district_name);
            $SENDER_WARD = ViettelPostUtils::getIDWardViettelPost($SENDER_DISTRICT, $addressPickupExists->wards_name);
            $SENDER_ADDRESS = "$addressPickupExists->address_detail, $addressPickupExists->wards_name, $addressPickupExists->district_name, $addressPickupExists->province_name";
        } else {
            $addressPickupExists = StoreAddress::where(
                'store_id',
                $orderDB->store_id
            )->where('is_default_pickup', true)->first();
            $SENDER_PROVINCE = ViettelPostUtils::getIDProvinceViettelPost($addressPickupExists->province_name);
            $SENDER_DISTRICT = ViettelPostUtils::getIDDistrictViettelPost($SENDER_PROVINCE, $addressPickupExists->district_name);
            $SENDER_WARD = ViettelPostUtils::getIDWardViettelPost($SENDER_DISTRICT, $addressPickupExists->wards_name);
            $SENDER_ADDRESS = "$addressPickupExists->address_detail, $addressPickupExists->wards_name, $addressPickupExists->district_name, $addressPickupExists->province_name";
        }


        $store = Store::where('id', $orderDB->store_id)->first();
        $order = [
            "ORDER_NUMBER" => $orderDB->order_code,
            "SENDER_FULLNAME" =>  $store->name,
            "SENDER_PHONE" =>  $addressPickupExists->phone,
            "SENDER_ADDRESS" =>  $SENDER_ADDRESS,
            // "SENDER_WARD" =>  $SENDER_WARD,
            // "SENDER_DISTRICT" =>  $SENDER_DISTRICT,
            // "SENDER_PROVINCE" =>  $SENDER_PROVINCE,
            "RECEIVER_FULLNAME" => $orderDB->customer_name,
            "RECEIVER_ADDRESS" =>  $RECEIVER_ADDRESS,
            "RECEIVER_PHONE" =>  $orderDB->customer_phone,
            // "RECEIVER_WARD" =>  $RECEIVER_WARD,
            // "RECEIVER_DISTRICT" =>  $RECEIVER_DISTRICT,
            // "RECEIVER_PROVINCE" =>  $RECEIVER_PROVINCE,
            "PRODUCT_TYPE" =>  "HH",
            "ORDER_PAYMENT" =>  $orderDB->ship_discount_amount > 0  || ($orderDB->remaining_amount == 0 && $orderDB->cod == 0) ? 3 : 2,
            "ORDER_SERVICE" =>  $orderDB->ship_speed_code ?? "LCOD",
            "MONEY_TOTALFEE" =>  0,
            "MONEY_FEECOD" =>  0,
            "MONEY_FEEVAS" =>  0,
            "MONEY_FEEINSURRANCE" =>  0,
            "MONEY_FEE" =>  0,
            "MONEY_FEEOTHER" =>  0,
            "MONEY_TOTALVAT" =>  0,
            "MONEY_TOTAL" => $orderDB->total_final - $orderDB->total_shipping_fee,
            'PRODUCT_NAME' => $nameProducts,
            'PRODUCT_WEIGHT' => $orderDB->package_weight ?? 100, // viettelPost cal by gam
            "PRODUCT_LENGTH" => $orderDB->package_length ?? 0,
            "PRODUCT_WIDTH" => $orderDB->package_width ?? 0,
            "PRODUCT_HEIGHT" => $orderDB->package_height ?? 0,
            'PRODUCT_PRICE' => $orderDB->total_final - $orderDB->total_shipping_fee,
            "MONEY_COLLECTION" => $orderDB->cod,
            "LIST_ITEM" => $LIST_ITEM
        ];

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        $dataGetListTrip =  $order;
        $dataGetListTrip["SENDER_WARD"] = $SENDER_WARD;
        $dataGetListTrip["SENDER_DISTRICT"] =  $SENDER_DISTRICT;
        $dataGetListTrip["SENDER_PROVINCE"] =  $SENDER_PROVINCE;
        $dataGetListTrip["RECEIVER_WARD"] = $RECEIVER_WARD;
        $dataGetListTrip["RECEIVER_DISTRICT"] = $RECEIVER_DISTRICT;
        $dataGetListTrip["RECEIVER_PROVINCE"] = $RECEIVER_PROVINCE;
        $dataGetListTrip["TYPE"] = 1;

        try {

            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => $order
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->status == 204) { // code status when Price does not apply to this itinerary
                try {
                    $response = $client->post(
                        "https://partner.viettelpost.vn/v2/order/getPriceAll",
                        [
                            'headers' => $headers,
                            'timeout'         => 15,
                            'connect_timeout' => 15,
                            'query' => [],
                            'json' => $dataGetListTrip
                        ]
                    );

                    $bodyTrip = (string) $response->getBody();
                } catch (Exception $e) {
                }

                if (str_contains($bodyTrip, 'PHS')) {
                    $order['ORDER_SERVICE'] = "PHS";
                }
                if (str_contains($bodyTrip, 'VCBO')) {
                    $order['ORDER_SERVICE'] = "VCBO";
                } else  if (str_contains($bodyTrip, 'LCOD')) {
                    $order['ORDER_SERVICE'] = "LCOD";
                } else if ($orderDB->customer_province_name == $addressPickupExists->province_name) {
                    $order['ORDER_SERVICE'] = "PHS";
                }


                $response = $client->post(
                    $send_order_url,
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'query' => [],
                        'json' => $order
                    ]
                );

                $body = (string) $response->getBody();
                $jsonResponse = json_decode($body);

                if ($jsonResponse->status != 200) {
                    return new Exception($jsonResponse->message);
                } else {
                    return [
                        'code' => $jsonResponse->data->ORDER_NUMBER,
                        'fee' => $jsonResponse->data->MONEY_TOTAL,
                    ];
                }
            } else if ($jsonResponse->status != 200) {
                return new Exception($jsonResponse->message);
            } else {
                return [
                    'code' => $jsonResponse->data->ORDER_NUMBER,
                    'fee' => $jsonResponse->data->MONEY_TOTAL,
                ];
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

    public static function send_order_vietnam_post($orderDB, $addressPickupExists, $token)
    {
        $SENDER_PROVINCE = null;
        $SENDER_DISTRICT = null;
        $SENDER_WARD = null;
        $SENDER_ADDRESS = null;
        $config = config('saha.shipper.list_shipper')[3];
        $send_order_url = $config["send_order_url"];
        $nameProducts = null;

        $handleToken = ViettelPostUtils::handleToken($token);
        if (empty($handleToken)) {
            return new Exception('error');
        }

        //Dữ liệu up
        $LIST_ITEM = array();
        foreach ($orderDB->line_items as $line_item) {
            $distribute = json_decode($line_item->distributes);
            $nameProducts .= $line_item->product->name;
            try {

                if (isset($distribute[0])) {
                    if (isset($distribute[0]->value) && !empty($distribute[0]->value)) {
                        $nameProducts .= ":" . $distribute[0]->value;
                    }

                    if (isset($distribute[0]->sub_element_distributes) && !empty($distribute[0]->sub_element_distributes)) {
                        $nameProducts .= ":" . $distribute[0]->sub_element_distributes;
                    }
                }
            } catch (\Throwable $th) {
            }
            $nameProducts .= " x " . $line_item->quantity . ", ";
            array_push(
                $LIST_ITEM,
                [
                    "PRODUCT_NAME" => $line_item->product->name,
                    "PRODUCT_PRICE" => $line_item->item_price,
                    "PRODUCT_WEIGHT" => ($line_item->product->weight <= 0 ? 100 : $line_item->product->weight),
                    "PRODUCT_QUANTITY" => $line_item->quantity,
                ]

            );
        }
        if ($nameProducts != null) {
            $nameProducts .= "...";
        }
        $RECEIVER_PROVINCE = VietnamPostUtils::getProvinceVietnamPost(null, $orderDB->customer_province_name);

        $RECEIVER_DISTRICT = VietnamPostUtils::getDistrictVietnamPost(null, $RECEIVER_PROVINCE['provinceCode'], $orderDB->customer_district_name);
        $RECEIVER_WARD = VietnamPostUtils::getWardVietnamPost(null, $RECEIVER_DISTRICT['districtCode'], $orderDB->customer_wards_name);

        $SENDER_PROVINCE = 1;
        $SENDER_DISTRICT = 1;
        $SENDER_WARD = 1;


        if ($addressPickupExists != null) {
            $SENDER_PROVINCE = VietnamPostUtils::getProvinceVietnamPost(null, $addressPickupExists->province_name);
            $SENDER_DISTRICT = VietnamPostUtils::getDistrictVietnamPost(null, $SENDER_PROVINCE['provinceCode'], $addressPickupExists->district_name);
            $SENDER_WARD = VietnamPostUtils::getWardVietnamPost(null, $SENDER_DISTRICT['districtCode'], $addressPickupExists->wards_name);
            $SENDER_ADDRESS = "$addressPickupExists->address_detail, $addressPickupExists->wards_name, $addressPickupExists->district_name, $addressPickupExists->province_name";
        }
        try {
            $additionRequest = [];

            if (!empty($orderDB->cod)) {
                array_push($additionRequest, [
                    "code" => "GTG021",
                    "propValue" => "PROP0018:" . $orderDB->cod
                ]);
            }
            $dataBody = [
                "orderCreationStatus" => 1, // 0 đơn nháp, 1 đơn thực
                "type" => "GUI",
                "customerCode" => $handleToken['customerCode'], // mã khách hàng
                "contractCode" => isset($handleToken['contractCode']) ? $handleToken['contractCode'] : "", // mã hợp đồng
                "informationOrder" => [
                    "senderName" => $addressPickupExists->name,
                    "senderPhone" => $addressPickupExists->phone,
                    "senderMail" => $addressPickupExists->email,
                    "senderAddress" => $SENDER_ADDRESS,
                    "senderProvinceCode" => $SENDER_PROVINCE['provinceCode'] ?? "",
                    "senderProvinceName" => $SENDER_PROVINCE['provinceName'] ?? "",
                    "senderDistrictCode" => $SENDER_DISTRICT['districtCode'] ?? "",
                    "senderDistrictName" => $SENDER_DISTRICT['districtName'] ?? "",
                    "senderCommuneCode" => $SENDER_WARD['communeCode'] ?? "",
                    "senderCommuneName" => $SENDER_WARD['communeName'] ?? "",
                    "receiverName" => $orderDB->customer_name,
                    "receiverAddress" => "$orderDB->customer_address_detail, $orderDB->customer_wards_name, $orderDB->customer_district_name, $orderDB->customer_province_name",
                    "receiverProvinceCode" => $RECEIVER_PROVINCE['provinceCode'] ?? "",
                    "receiverProvinceName" => $RECEIVER_PROVINCE['provinceName'] ?? "",
                    "receiverDistrictCode" => $RECEIVER_DISTRICT['districtCode'] ?? "",
                    "receiverDistrictName" => $RECEIVER_DISTRICT['districtName'] ?? "",
                    "receiverCommuneCode" => $RECEIVER_WARD['communeCode'] ?? "",
                    "receiverCommuneName" => $RECEIVER_WARD['communeName'] ?? "",
                    "receiverPhone" => $orderDB->customer_phone,
                    "receiverEmail" => null,
                    "addonService" => [],
                    "additionRequest" => $additionRequest,
                    "orgCodeCollect" => null,
                    "serviceCode" => $orderDB->ship_speed_code ?? config('saha.shipper.list_shipper')[3]['ship_speed_code_default'],
                    "orgCodeAccept" => null,
                    "saleOrderCode" => $orderDB->order_code,
                    "nameProducts" => $nameProducts,
                    "contentNote" => $nameProducts,
                    "weight" => $orderDB->package_weight, // cal by gam
                    "width" => $orderDB->package_width,
                    "length" => $orderDB->package_length,
                    "height" => $orderDB->package_height,
                    "vehicle" => null,
                    "sendType" => "1",
                    "isBroken" => "0",
                    "deliveryTime" => "N",
                    "deliveryRequire" => "1",
                    "deliveryInstruction" => $orderDB->customer_note,
                ]
            ];
        } catch (Exception $e) {

            return new Exception('error');
        }
        //////
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $handleToken['token'],
        ];
        $bodyTrip = "";
        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'json' => $dataBody
                ]
            );

            $body = (string) $response->getBody();
            $json = json_decode($body);
            $bodyTrip  = $body;
            if (isset($json->originalID)) {
                return [
                    'code' => $json->originalID,
                    'fee' => $json->totalFee,
                ];
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

    public static function send_order_nhattin($orderDB, $addressPickupExists, $token)
    {
        $SENDER_PROVINCE = null;
        $SENDER_DISTRICT = null;
        $SENDER_WARD = null;
        $SENDER_ADDRESS = null;
        $COUNT_PACKAGE = 0;
        $config = config('saha.shipper.list_shipper')[4];
        $send_order_url = $config["send_order_url"];
        $nameProducts = null;

        $handleToken = NhattinPostUtils::handleTokenToAccount($token);

        if (empty($handleToken)) {
            return new Exception('error');
        }

        //Dữ liệu up
        foreach ($orderDB->line_items as $line_item) {
            $distribute = json_decode($line_item->distributes);
            $nameProducts .= $line_item->product->name;
            try {

                if (isset($distribute[0])) {
                    if (isset($distribute[0]->value) && !empty($distribute[0]->value)) {
                        $nameProducts .= ":" . $distribute[0]->value;
                    }

                    if (isset($distribute[0]->sub_element_distributes) && !empty($distribute[0]->sub_element_distributes)) {
                        $nameProducts .= ":" . $distribute[0]->sub_element_distributes;
                    }
                }
            } catch (\Throwable $th) {
            }
            $nameProducts .= " x " . $line_item->quantity . ", ";
            $COUNT_PACKAGE++;
        }
        if ($nameProducts != null) {
            $nameProducts .= "...";
        }

        // Handle address of receiver
        $RECEIVER_PROVINCE = NhattinPostUtils::getProvinceNhattinPost($handleToken, $orderDB->customer_province_name);
        $RECEIVER_DISTRICT = NhattinPostUtils::getDistrictNhattinPost($handleToken, $RECEIVER_PROVINCE['province_code'], $orderDB->customer_district_name);
        $RECEIVER_WARD = NhattinPostUtils::getWardNhattinPost($handleToken, $RECEIVER_DISTRICT['district_code'], $orderDB->customer_wards_name);
        $RECEIVER_ADDRESS = $orderDB->customer_address_detail;

        $SENDER_PROVINCE = 1;
        $SENDER_DISTRICT = 1;
        $SENDER_WARD = 1;

        // Handle address of sender
        if ($addressPickupExists != null) {
            $SENDER_PROVINCE = NhattinPostUtils::getProvinceNhattinPost($handleToken, $addressPickupExists->province_name);
            $SENDER_DISTRICT = NhattinPostUtils::getDistrictNhattinPost($handleToken, $SENDER_PROVINCE['province_code'], $addressPickupExists->district_name);
            $SENDER_WARD = NhattinPostUtils::getWardNhattinPost($handleToken, $SENDER_DISTRICT['district_code'], $addressPickupExists->wards_name);
            $SENDER_ADDRESS = $addressPickupExists->address_detail;
        }

        // Handle weight into gram - Xử lý cân nặng về gram
        if ($orderDB->package_weight > 0) {
            $weight = $orderDB->package_weight / 1000;
        } else {
            $weight = $orderDB->package_weight ?? 0;
        }

        // Handle method payment receive or sender paid (receive paid: 20, sender paid: 10)
        if ($orderDB->ship_discount_amount > 0 || ($orderDB->remaining_amount == 0 && $orderDB->cod == 0)) {
            $paymentMethodId = 10;
        } else {
            $paymentMethodId = 20;
        }

        $dataBody = [
            "partner_id" => $handleToken['partner_id'],
            "ref_code" => $orderDB->order_code,
            "weight" =>  $weight, // cal by kilogram
            "width" => $orderDB->package_width,
            "length" => $orderDB->package_length,
            "height" => $orderDB->package_height,
            "service_id" => $orderDB->ship_speed_code,
            "package_no" => $COUNT_PACKAGE,
            "payment_method_id" => $paymentMethodId,
            "cod_amount" => 0,
            "cargo_value" => 0,
            "cargo_type_id" => 2, // 1 - Documents,  2 - Goods, 3 - Cold goods, 4 - Biological products, 5 - Specimens
            "s_name" =>  $addressPickupExists->name,
            "s_phone" =>  $addressPickupExists->phone,
            "s_address" =>  $SENDER_ADDRESS,
            "s_ward_name" =>  $SENDER_WARD['ward_name'],
            "s_district_name" =>  $SENDER_DISTRICT['district_name'],
            "s_province_name" =>  $SENDER_PROVINCE['province_name'],
            "r_name" => $orderDB->customer_name,
            "r_phone" => $orderDB->customer_phone,
            "r_address" => $RECEIVER_ADDRESS,
            "r_ward_name" => $RECEIVER_WARD['ward_name'],
            "r_district_name" => $RECEIVER_DISTRICT['district_name'],
            "r_province_name" => $RECEIVER_PROVINCE['province_name'],
            "note" => $orderDB->customer_note,
            "cargo_content" => $nameProducts,
            "cod_amount" => $orderDB->cod,
        ];
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
            'username' => $handleToken['username'],
            'password' => $handleToken['password'],
            'partner_id' => $handleToken['partner_id']
        ];

        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'json' => $dataBody
                ]
            );

            $body = (string) $response->getBody();
            $json = json_decode($body);

            if (!$json->success) {
                return new Exception($json->message);
            }

            if (isset($json->data)) {
                return [
                    'code' => $json->data->bill_code,
                    'fee' => $json->data->total_fee,
                ];
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

    public static function cancel_order_vietnam_post($orderDB, $token)
    {
        $OriginalId = null;
        $config = config('saha.shipper.list_shipper')[3];
        $cancel_order_url = $config["cancel_order"];

        $handleToken = ViettelPostUtils::handleToken($token);

        if (empty($handleToken)) {
            return new Exception('error');
        }



        $orderShipperCode = OrderShiperCode::where('order_id', $orderDB->id)->first();
        if ($orderShipperCode) {
            $OriginalId = $orderShipperCode->from_shipper_code;

            if (empty($OriginalId)) {
                return new Exception('error');
            }

            $dataBody = [
                "OriginalId" => $OriginalId, // mã đơn gốc của vietnampost
            ];

            $client = new Client();
            $headers = [
                'Content-Type' => 'application/json',
                'token' => $handleToken['token'],
            ];

            try {
                $response = $client->post(
                    $cancel_order_url,
                    [
                        'headers' => $headers,
                        'json' => $dataBody
                    ]
                );

                $body = (string) $response->getBody();
                $json = json_decode($body);
                if (isset($json->status)) {
                    return new Exception('error');
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
    }

    public static function cancel_order_viettel_post($orderDB, $token)
    {
        $config = config('saha.shipper.list_shipper')[2];
        $cancel_order_url = $config["cancel_order"];


        $orderShipperCode = OrderShiperCode::where('order_id', $orderDB->id)->first();
        if ($orderShipperCode) {
            $OriginalId = $orderShipperCode->from_shipper_code;

            if (empty($OriginalId)) {
                return new Exception('error');
            }

            $dataBody = [
                "ORDER_NUMBER" => $OriginalId, // mã đơn gốc của viettelPost
                "TYPE" => 4, // TYPE update bill of viettelPost(1: Confirm order, 2 confirm return shipping, 3: delivery again, 4: cancel order, 5: get back order(reorder), 11. delete canceled order)
                "NOTE" => "Shop hủy lấy"
            ];

            $client = new Client();
            $headers = [
                'Content-Type' => 'application/json',
                'token' => $token,
            ];

            try {
                $response = $client->post(
                    $cancel_order_url,
                    [
                        'headers' => $headers,
                        'json' => $dataBody
                    ]
                );

                $body = (string) $response->getBody();
                $json = json_decode($body);

                if (isset($json->status) && $json->status != 200 || $json->error == true) {
                    return new Exception('error');
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
    }

    public static function cancel_order_nhattin($orderDB, $token)
    {
        $config = config('saha.shipper.list_shipper')[4];

        $cancel_order_url = $config["cancel_order"];

        $handleToken = NhattinPostUtils::handleTokenToAccount($token);
        if (empty($handleToken)) {
            return new Exception('error');
        }
        $orderShipperCode = OrderShiperCode::where('order_id', $orderDB->id)->first();

        if ($orderShipperCode) {
            $orderCodeNhattin = $orderShipperCode->from_shipper_code;

            if (empty($orderCodeNhattin)) {
                return new Exception('error');
            }

            $dataBody = [
                "bill_code" => [$orderCodeNhattin], // mã đơn gốc của vietnampost
            ];

            $client = new Client();
            $headers = [
                'Content-Type' => 'application/json',
                'token' => $token,
                'username' => $handleToken['username'],
                'password' => $handleToken['password'],
                'partner_id' => $handleToken['partner_id']
            ];
            try {
                $response = $client->post(
                    $cancel_order_url,
                    [
                        'headers' => $headers,
                        'json' => $dataBody
                    ]
                );

                $body = (string) $response->getBody();
                $json = json_decode($body);

                if (isset($json->success) && $json->success == false) {
                    return new Exception('error');
                }

                if (isset($json->failed) && !empty($json->failed) && isset($json->failed[0])) {
                    return $json->failed[0]["message"];
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {

                if ($e->hasResponse()) {

                    $body = (string) $e->getResponse()->getBody();
                    $statusCode = $e->getResponse()->getStatusCode();

                    $jsonResponse = json_decode($body);

                    return new Exception($jsonResponse->error);
                }
                return new Exception('error');
            } catch (Exception $e) {

                return new Exception('error');
            }
        }
    }
}
