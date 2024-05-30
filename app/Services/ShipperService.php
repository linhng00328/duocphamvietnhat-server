<?php

namespace App\Services;

use App\Helper\Place;
use App\Http\Controllers\Api\User\ConfigShipController;
use App\Models\MsgCode;
use App\Models\Shipment;
use App\Services\Shipper\GHN\GHNUtils;
use App\Services\Shipper\NhattinPost\NhattinPostUtils;
use App\Services\Shipper\VietnamPost\DataVietnamPost;
use App\Services\Shipper\VietnamPost\VietnamPostUtils;
use App\Services\Shipper\ViettelPost\ViettelPostUtils;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleClient;


//0 tieu chuan
//1 sieu toc

class ShipperService
{

    static function check_token_partner_id($partner_id, $token)
    {



        $data = null;
        if ((int)$partner_id  == 0) {
            $data  =   ShipperService::check_token_ghtk($token);
        }

        if ((int)$partner_id  == 1) {
            $data  =   ShipperService::check_token_ghn($token);
        }

        if ((int)$partner_id  == 2) {
            $data  =   ShipperService::check_token_viettel($token);
        }
        if ((int)$partner_id  == 3) {
            // $data  =   ShipperService::check_token_vietnam_post($token);
        }

        return $data;
    }

    static function caculate_monney_all_and_manule($shipperArr)
    {

        $datas = config('saha.shipper.list_shipper');
        $listShip = [];
        $listShipFile = [];

        foreach ($datas as $shiperInFile) {
            $partnerExists = Shipment::where('store_id', $shipperArr['store_id'])
                ->where('partner_id', $shiperInFile['id'])
                ->where('use', true)
                ->whereNotNull('token')
                ->first();

            if ($partnerExists != null) {

                $listShipFile[$shiperInFile['id']] = $shiperInFile;
                array_push($listShip, [
                    'id' => $shiperInFile['id'],
                    'name' => $shiperInFile['name'],
                    'ship_speed' => $shiperInFile['ship_speed'],
                    'shipper_config' => $partnerExists
                ]);
            }
        }


        if (count($listShip) == 0) {
            return [
                'info' => "Chưa cài đặt đơn vị vận chuyển",
                "sucess" => false,
                'data' => []
            ];
        }

        $data = array();
        $info = null;


        foreach ($listShip as $shiperDB) {

            $partner_id = $shiperDB['shipper_config']->partner_id;
            $token = $shiperDB['shipper_config']->token;

            $ship_speed = $shiperDB['ship_speed'];
            $name = $shiperDB['name'];


            //Giao tiêu chuẩn
            $res  =   ShipperService::caculate_monney_one_partner(
                $shipperArr,
                $partner_id,
                0,
                $token
            );

            if ($res instanceof Exception) {
                array_push(
                    $data,
                    array(
                        "partner_id" =>    $partner_id,
                        "fee" => 0,
                        "name" => $name,
                        "image" =>  $listShipFile[$partner_id]['image_url'],
                        "ship_type" => 0,
                        "success" => false,
                        'info' => $res->getMessage()
                    )
                );
            } else {
                array_push(
                    $data,
                    array(
                        "partner_id" =>    $partner_id,
                        "fee" => $res,
                        "name" => $name,
                        "image" =>  $listShipFile[$partner_id]['image_url'],
                        "ship_type" => 0,
                        "success" => true,
                        'info' => ""
                    )
                );
            }
        }


        return [
            'info' => $info,
            'data' =>  $data
        ];
    }

    static function caculate_monney_all($shipperArr)
    {
        $datas = config('saha.shipper.list_shipper');
        $listShip = [];

        foreach ($datas as $shiperInFile) {
            $partnerExists = Shipment::where('store_id', $shipperArr['store_id'])
                ->where('partner_id', $shiperInFile['id'])
                ->where('use', true)
                ->whereNotNull('token')
                ->first();

            if ($partnerExists != null) {

                array_push($listShip, [
                    'id' => $shiperInFile['id'],
                    'logo' => $shiperInFile['image_url'],
                    'name' => $shiperInFile['name'],
                    'ship_speed' => $shiperInFile['ship_speed'],
                    'shipper_config' => $partnerExists
                ]);
            }
        }

        if (count($listShip) == 0) {
            return [
                'info' => "Chưa cài đặt đơn vị vận chuyển",
                'data' => []
            ];
        }

        $data = array();
        $info = null;

        foreach ($listShip as $shiperDB) {

            $partner_id = $shiperDB['shipper_config']->partner_id;
            $token = $shiperDB['shipper_config']->token;
            $ship_speed = $shiperDB['ship_speed'];
            $name = $shiperDB['name'];


            //Giao tiêu chuẩn
            $res  =   ShipperService::caculate_monney_one_partner(
                $shipperArr,
                $partner_id,
                0,
                $token
            );

            if ($res instanceof Exception) {
                $info = $res->getMessage();
            } else {
                array_push(
                    $data,
                    array(
                        "partner_id" =>    $partner_id,
                        "fee" => $res,
                        "name" => $name,
                        "ship_type" => 0,
                        "description" => "Dự kiến " . ((Carbon::now()->addDays(1)->format('d')) . " Th" . Carbon::now()->addDays(1)->format('m')) . " - " . ((Carbon::now()->addDays(4)->format('d')) . " Th" . Carbon::now()->addDays(4)->format('m'))
                    )
                );
            }
        }


        return [
            'info' => $info,
            'data' =>  $data
        ];
    }

    static function caculate_monney_one_partner($shipperArr, $partner_id, $type_ship, $token)
    {
        $data = null;
        if ($partner_id  == 0) {
            $data  =   ShipperService::caculate_monney_ghtk($shipperArr, $type_ship, $token);
        }

        if ($partner_id  == 1) {
            $data  =   ShipperService::caculate_monney_ghn($shipperArr, $type_ship, $token);
        }

        if ($partner_id  == 2) {
            $data  =   ShipperService::caculate_monney_viettel($shipperArr, $type_ship, $token);
        }
        if ($partner_id  == 3) {
            $data  =   ShipperService::caculate_monney_vietnam_post($shipperArr, $type_ship, $token);
        }
        if ($partner_id  == 4) {
            $data  =   ShipperService::caculate_monney_nhattin($shipperArr, $type_ship, $token);
        }


        return $data;
    }

    public static function check_token_ghtk($token)
    {
        $config = config('saha.shipper.list_shipper')[0];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]
            );

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode == 401) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function caculate_monney_ghtk($shipperArr, $type_ship, $token)
    {

        $typeShip = 'none'; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = 'none';
        } else if ($type_ship == 1) {
            $typeShip = "xteam";
        }

        return ShipperService::res_ghtk($shipperArr,  $typeShip, $token);
    }

    public static function res_ghtk($shipperArr,  $typeShip, $token)
    {
        $config = config('saha.shipper.list_shipper')[0];
        $fee_url = $config["fee_url"];


        $client = new GuzzleClient();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'query' => [
                        'pick_province' => Place::getNameProvince($shipperArr["from_province_id"]),
                        'pick_district' => Place::getNameDistrict($shipperArr["from_district_id"]),
                        'province' => Place::getNameProvince($shipperArr["to_province_id"]),
                        'district' => Place::getNameDistrict($shipperArr["to_district_id"]),
                        'deliver_option' =>  $typeShip,
                        'transport' => 'road',
                        'weight' => $shipperArr["weight"],
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->success == false) {
                return new Exception($jsonResponse->message);
            } else {
                if ($jsonResponse->fee == null || !isset($jsonResponse->fee->fee)) {

                    return new Exception("null");
                }
                return $jsonResponse->fee->fee;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 0)->update(
                            [
                                'use' => false,
                            ]
                        );
                }
                return new Exception($jsonResponse->message);
            }
            return new Exception('error');
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function check_token_ghn($token)
    {

        $config = config('saha.shipper.list_shipper')[1];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]

            );

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode == 401) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function check_token_viettel($token)
    {

        $config = config('saha.shipper.list_shipper')[2];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]

            );
            $data = json_decode($response->getBody());
            if ($data->status != 200) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode != 200) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function check_token_vietnam_post($token)
    {

        $config = config('saha.shipper.list_shipper')[2];
        $fee_url = $config["check_token_url"];

        $handleToken = ViettelPostUtils::handleToken($token);

        if (empty($handleToken)) {
            return json_decode("[]");
        }

        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $handleToken['token'],
        ];

        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]

            );
            $data = json_decode($response->getBody());
            if ($data->status != 200) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode != 200) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function get_list_price_and_type_ghtk($shipperArr, $type_ship, $token)
    {
        $list_service = json_decode('[]');

        $price1 =   ShipperService::res_ghtk($shipperArr,  'none', $token) ?? 0;

        if (!($price1 instanceof Exception) && $price1  > 0) {
            array_push($list_service, [
                'ship_speed_code' => 'none',
                'description' => "Giao hàng tiêu chuẩn 2-5 ngày",
                'fee' =>  $price1,
            ]);
        };


        $price2 =  ShipperService::res_ghtk($shipperArr,  "xteam", $token) ?? 0;
        if (!($price2 instanceof Exception) && $price2  > 0) {
            array_push($list_service, [
                'ship_speed_code' => "xteam",
                'description' => "Giao hàng siêu tốc Xfast 24h",
                'fee' => $price2,
            ]);
        };

        return  $list_service;
    }

    public static function caculate_monney_ghn($shipperArr, $type_ship, $token)
    {

        $config = config('saha.shipper.list_shipper')[1];
        $fee_url = $config["fee_url"];


        $client = new GuzzleClient();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        $provinceNameFrom = Place::getNameProvince($shipperArr["from_province_id"]);

        // if ($value["ProvinceName"] == $name || in_array($name,$value["NameExtension0"])==true ) {
        $provinceNameTo = Place::getNameProvince($shipperArr["to_province_id"]);

        $provinceIdFrom = GHNUtils::getIDProvinceGHN($provinceNameFrom);
        $provinceIdTo = GHNUtils::getIDProvinceGHN($provinceNameTo);




        $districtNameFrom = Place::getNameDistrict($shipperArr["from_district_id"]);
        $districtNameTo = Place::getNameDistrict($shipperArr["to_district_id"]);

        $districtIdFrom = GHNUtils::getIDDistrictGHN($provinceIdFrom, $districtNameFrom);
        $districtIdTo = GHNUtils::getIDDistrictGHN($provinceIdTo, $districtNameTo);


        $typeShip = 2; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = 2;
        } else if ($type_ship == 1) {
            $typeShip = 1;
        }

        try {
            $response = $client->request(
                'GET',
                $fee_url,

                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'query' => [
                        'from_district_id' => $districtIdFrom,
                        'to_district_id' => $districtIdTo,
                        'service_type_id' => $typeShip,

                        'weight' => $shipperArr["weight"],
                        'height' => $shipperArr["height"],
                        'length' => $shipperArr["length"],
                        'width' => $shipperArr["width"],
                    ]

                ]

            );


            $body = (string) $response->getBody();


            $jsonResponse = json_decode($body);

            if ($jsonResponse->message != "Success") {

                return new Exception($jsonResponse->message);
            } else {

                if ($jsonResponse->data == null || !isset($jsonResponse->data->total)) {
                    return new Exception("null");
                }

                return $jsonResponse->data->total;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);


                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 1)->update(
                            [
                                'use' => false,
                            ]
                        );
                }

                return new Exception($jsonResponse->message);
            }


            return new Exception('error');
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function get_list_price_and_type_ghn($shipperArr, $type_ship, $token)
    {

        $config = config('saha.shipper.list_shipper')[1];
        $fee_url = $config["fee_url"];


        $client = new GuzzleClient();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        $provinceNameFrom = Place::getNameProvince($shipperArr["from_province_id"]);

        // if ($value["ProvinceName"] == $name || in_array($name,$value["NameExtension0"])==true ) {
        $provinceNameTo = Place::getNameProvince($shipperArr["to_province_id"]);

        $provinceIdFrom = GHNUtils::getIDProvinceGHN($provinceNameFrom);
        $provinceIdTo = GHNUtils::getIDProvinceGHN($provinceNameTo);


        $districtNameFrom = Place::getNameDistrict($shipperArr["from_district_id"]);
        $districtNameTo = Place::getNameDistrict($shipperArr["to_district_id"]);

        $districtIdFrom = GHNUtils::getIDDistrictGHN($provinceIdFrom, $districtNameFrom);
        $districtIdTo = GHNUtils::getIDDistrictGHN($provinceIdTo, $districtNameTo);


        if ($type_ship == null) {
            $typeShip = 2; //tiêu chuẩn

            if ($type_ship == 0) {
                $typeShip = 2;
            } else if ($type_ship == 1) {
                $typeShip = 1;
            }
        } else {
            $typeShip = $type_ship;
        }



        try {
            $response = $client->request(
                'GET',
                'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/available-services',

                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'query' => [
                        'shop_id' => 885,
                        'from_district' => $districtIdFrom,
                        'to_district' => $districtIdTo,
                        'service_type_id' => $typeShip,

                        'weight' => $shipperArr["weight"],
                        'height' => $shipperArr["height"],
                        'length' => $shipperArr["length"],
                        'width' => $shipperArr["width"],
                    ]

                ]

            );


            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            $list_service = json_decode("[]");

            foreach ($jsonResponse->data  as $item) {

                $price  =   ShipperService::caculate_monney_ghn($shipperArr, $item->service_type_id, $token);


                if ($price  > 0) {
                    array_push($list_service, [
                        'ship_speed_code' => $item->service_type_id,
                        'description' => $item->short_name,
                        'fee' =>  $price,

                    ]);
                }
            }

            return  $list_service;
        } catch (Exception $e) {

            return json_decode("[]");
        }
    }

    public static function get_list_price_and_type_viettel($shipperArr, $type_ship, $token)
    {
        $config = config('saha.shipper.list_shipper')[2];
        $fee_url = $config["fee_url"];

        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        $from_province_id = ViettelPostUtils::getIDProvinceViettelPost(Place::getNameProvince($shipperArr["from_province_id"]));
        $from_district_id = ViettelPostUtils::getIDDistrictViettelPost($from_province_id, Place::getNameDistrict($shipperArr["from_district_id"]));

        $to_province_id = ViettelPostUtils::getIDProvinceViettelPost(Place::getNameProvince($shipperArr["to_province_id"]));
        $to_district_id = ViettelPostUtils::getIDDistrictViettelPost($to_province_id, Place::getNameDistrict($shipperArr["to_district_id"]));

        $typeShip = "VCN"; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = "VCN";
        } else if ($type_ship == 1) {
            $typeShip = "VHT";
        }

        // dd([
        //     "PRODUCT_WEIGHT" => 7500,
        //     "PRODUCT_PRICE" => 200000,

        //     "ORDER_SERVICE_ADD" => "",
        //     "ORDER_SERVICE" => $typeShip,
        //     "SENDER_PROVINCE" =>   $from_province_id,
        //     "SENDER_DISTRICT" =>   $from_district_id,
        //     "RECEIVER_PROVINCE" => $to_province_id,
        //     "RECEIVER_DISTRICT" =>  $to_district_id,
        //     "PRODUCT_TYPE" => "HH",
        //     "NATIONAL_TYPE" => 1
        // ]);

        $dataStrip =  [

            "SENDER_PROVINCE" =>   $from_province_id,
            "SENDER_DISTRICT" =>   $from_district_id,
            "RECEIVER_PROVINCE" => $to_province_id,
            "RECEIVER_DISTRICT" =>  $to_district_id,

            "PRODUCT_TYPE" => "HH",
            "PRODUCT_WEIGHT" => $shipperArr["weight"] ?? 100,
            "PRODUCT_PRICE" => $shipperArr["total_final"] ?? 20000,
            "MONEY_COLLECTION" => $shipperArr["total_final"] ?? 20000,
            "TYPE" => 1

        ];


        $dataSend =  [
            "PRODUCT_WEIGHT" => $shipperArr["weight"] ?? 100,
            "PRODUCT_PRICE" => $shipperArr["total_final"] ?? 20000,

            "NATIONAL_TYPE" => 1,
            "PRODUCT_TYPE" =>  "HH",
            "ORDER_PAYMENT" =>  2,

            "MONEY_TOTALFEE" =>  0,
            "MONEY_FEECOD" =>  0,
            "MONEY_FEEVAS" =>  0,
            "MONEY_FEEINSURRANCE" =>  0,

            "MONEY_FEE" =>  0,
            "MONEY_FEEOTHER" =>  1000,
            "MONEY_TOTALVAT" =>  0,

            "SENDER_PROVINCE" =>   $from_province_id,
            "SENDER_DISTRICT" =>   $from_district_id,
            "RECEIVER_PROVINCE" => $to_province_id,
            "RECEIVER_DISTRICT" =>  $to_district_id,
            "MONEY_TOTAL" => $shipperArr["total_final"] ?? 20000,
            "MONEY_COLLECTION" => $shipperArr["total_final"] ?? 20000,
        ];

        try {
            $bodyTrip = "";
            try {
                $response = $client->post(
                    "https://partner.viettelpost.vn/v2/order/getPriceAll",
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'query' => [],
                        'json' => $dataStrip
                    ]
                );
                $body = (string) $response->getBody();

                $bodyTrip  = $body;
            } catch (Exception $e) {
            }

            $list_service = json_decode("[]");

            $jsonResponse = json_decode($body);
            foreach ($jsonResponse  as $item) {

                array_push($list_service, [
                    'ship_speed_code' => $item->MA_DV_CHINH,
                    'description' => $item->TEN_DICHVU . " " . $item->THOI_GIAN,
                    'fee' => $item->GIA_CUOC,
                ]);
            }

            return   $list_service;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return json_decode("[]");
            return new Exception('error');
        } catch (Exception $e) {

            return json_decode("[]");
            return new Exception('error');
        }
    }

    public static function caculate_monney_viettel($shipperArr, $type_ship, $token)
    {
        $config = config('saha.shipper.list_shipper')[2];
        $fee_url = $config["fee_url"];

        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        $from_province_id = ViettelPostUtils::getIDProvinceViettelPost(Place::getNameProvince($shipperArr["from_province_id"]));
        $from_district_id = ViettelPostUtils::getIDDistrictViettelPost($from_province_id, Place::getNameDistrict($shipperArr["from_district_id"]));

        $to_province_id = ViettelPostUtils::getIDProvinceViettelPost(Place::getNameProvince($shipperArr["to_province_id"]));
        $to_district_id = ViettelPostUtils::getIDDistrictViettelPost($to_province_id, Place::getNameDistrict($shipperArr["to_district_id"]));

        $typeShip = "VCN"; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = "VCN";
        } else if ($type_ship == 1) {
            $typeShip = "VHT";
        }

        // dd([
        //     "PRODUCT_WEIGHT" => 7500,
        //     "PRODUCT_PRICE" => 200000,

        //     "ORDER_SERVICE_ADD" => "",
        //     "ORDER_SERVICE" => $typeShip,
        //     "SENDER_PROVINCE" =>   $from_province_id,
        //     "SENDER_DISTRICT" =>   $from_district_id,
        //     "RECEIVER_PROVINCE" => $to_province_id,
        //     "RECEIVER_DISTRICT" =>  $to_district_id,
        //     "PRODUCT_TYPE" => "HH",
        //     "NATIONAL_TYPE" => 1
        // ]);

        $dataStrip =  [

            "SENDER_PROVINCE" =>   $from_province_id,
            "SENDER_DISTRICT" =>   $from_district_id,
            "RECEIVER_PROVINCE" => $to_province_id,
            "RECEIVER_DISTRICT" =>  $to_district_id,

            "PRODUCT_TYPE" => "HH",
            "PRODUCT_WEIGHT" => $shipperArr["weight"] ?? 100,
            "PRODUCT_PRICE" => $shipperArr["total_final"] ?? 20000,
            "MONEY_COLLECTION" => $shipperArr["total_final"] ?? 20000,
            "TYPE" => 1

        ];


        $dataSend =  [
            "PRODUCT_WEIGHT" => $shipperArr["weight"] ?? 100,
            "PRODUCT_PRICE" => $shipperArr["total_final"] ?? 20000,

            "NATIONAL_TYPE" => 1,
            "PRODUCT_TYPE" =>  "HH",
            "ORDER_PAYMENT" =>  2,

            "MONEY_TOTALFEE" =>  0,
            "MONEY_FEECOD" =>  0,
            "MONEY_FEEVAS" =>  0,
            "MONEY_FEEINSURRANCE" =>  0,

            "MONEY_FEE" =>  0,
            "MONEY_FEEOTHER" =>  1000,
            "MONEY_TOTALVAT" =>  0,

            "SENDER_PROVINCE" =>   $from_province_id,
            "SENDER_DISTRICT" =>   $from_district_id,
            "RECEIVER_PROVINCE" => $to_province_id,
            "RECEIVER_DISTRICT" =>  $to_district_id,
            "MONEY_TOTAL" => $shipperArr["total_final"] ?? 20000,
            "MONEY_COLLECTION" => $shipperArr["total_final"] ?? 20000,
        ];

        try {
            $bodyTrip = "";
            try {
                $response = $client->post(
                    "https://partner.viettelpost.vn/v2/order/getPriceAll",
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'query' => [],
                        'json' => $dataStrip
                    ]
                );
                $body = (string) $response->getBody();

                $bodyTrip  = $body;
            } catch (Exception $e) {
            }

            $description = null;
            if (str_contains($bodyTrip, 'PHS')) {
                $dataSend['ORDER_SERVICE'] = "PHS";
            }
            if (str_contains($bodyTrip, 'VCBO')) {
                $dataSend['ORDER_SERVICE'] = "VCBO";
            } else  if (str_contains($bodyTrip, 'LCOD')) {
                $dataSend['ORDER_SERVICE'] = "LCOD";
            } else {
                $dataSend['ORDER_SERVICE'] = "PHS";
            }



            $response = $client->post(
                $fee_url,
                [

                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'json' => $dataSend

                ]

            );


            $body = (string) $response->getBody();

            $jsonResponse = json_decode($body);


            if ($jsonResponse->data == null) {

                return new Exception($jsonResponse->message);
            } else {


                if ($jsonResponse->data == null || !isset($jsonResponse->data->MONEY_TOTAL)) {
                    return new Exception("null");
                }

                return $jsonResponse->data->MONEY_TOTAL;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 1)->update(
                            [
                                'use' => false,
                            ]
                        );
                }

                return new Exception($jsonResponse);
            }


            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }

    public static function get_list_price_and_type_vietnam_post($shipperArr, $type_ship, $token)
    {
        $config = config('saha.shipper.list_shipper')[3];
        $fee_url = $config["fee_url"];
        $client = new GuzzleClient();
        $body = null;
        $bodyTrip = "";

        $handleToken = ViettelPostUtils::handleToken($token);

        if (empty($handleToken)) {
            return json_decode("[]");
        }

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $handleToken['token'],
        ];

        $from_province = VietnamPostUtils::getProvinceVietnamPost($token, Place::getNameProvince($shipperArr["from_province_id"]));
        $from_district = VietnamPostUtils::getDistrictVietnamPost($token, $from_province['provinceCode'], Place::getNameDistrict($shipperArr["from_district_id"]));
        $from_ward = VietnamPostUtils::getWardVietnamPost($token, $from_district['districtCode'], Place::getNameWards($shipperArr["from_wards_id"]));
        $to_province = VietnamPostUtils::getProvinceVietnamPost($token, Place::getNameProvince($shipperArr["to_province_id"]));
        $to_district = VietnamPostUtils::getDistrictVietnamPost($token, $to_province['provinceCode'], Place::getNameDistrict($shipperArr["to_district_id"]));
        $to_ward = VietnamPostUtils::getWardVietnamPost($token, $to_district['districtCode'], Place::getNameWards($shipperArr["to_wards_id"]));

        $dataStrip =  [
            "scope" => 1, // 1 vn, 2 quốc tế
            "customerCode" => $handleToken['customerCode'], // mã khách hàng
            "contractCode" => isset($handleToken['contractCode']) ? $handleToken['contractCode'] : "",
            "data" => [
                "senderProvinceCode" => $from_province ? $from_province['provinceCode'] : null,
                "senderProvinceName" => $from_province ?  $from_province['provinceName'] : null,
                "senderDistrictCode" => $from_district ? $from_district['districtCode'] : null,
                "senderDistrictName" => $from_district ? $from_district['districtName'] : null,
                "senderCommuneCode" => $from_ward ? $from_ward['communeCode'] : null,
                "senderCommuneName" => $from_ward ? $from_ward['communeName'] : null,
                "receiverProvinceCode" => $to_province ? $to_province['provinceCode'] : null,
                "receiverProvinceName" => $to_province ? $to_province['provinceName'] : null,
                "receiverDistrictCode" => $to_district ? $to_district['districtCode'] : null,
                "receiverDistrictName" => $to_district ? $to_district['districtName'] : null,
                "receiverCommuneCode" => $to_ward ? $to_ward['communeCode'] : null,
                "receiverCommuneName" => $to_ward ? $to_ward['communeName'] : null,
                "receiverName" => $shipperArr['customer_name'],
                "receiverAddress" => $shipperArr['to_address_detail'],
                "receiverNational" => "VN",
                "receiverCity" => null,
                "orgCodeAccept" => null,
                "receiverPostCode" => null,
                "weight" => $shipperArr["weight"] ?? 0,
                "width" => $shipperArr["width"] ?? 0,
                "length" => $shipperArr["length"] ?? 0,
                "height" => $shipperArr["height"] ?? 0,
                "serviceCode" => null,
                "addRequest" => [],
                "addonService" => [],
                "additionRequest" => [],
                "vehicle" => null
            ]
        ];
        try {
            try {
                $response = $client->post(
                    "https://connect-my.vnpost.vn/customer-partner/ServicesCharge",
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'json' => $dataStrip,
                    ]
                );
                $body = (string) $response->getBody();

                $bodyTrip  = $body;
            } catch (Exception $e) {
            }

            $list_service = json_decode("[]");

            $jsonResponse = json_decode($body);

            foreach ($jsonResponse  as $item) {
                if (isset(DataVietnamPost::codeGTGT[$item->serviceCode])) {
                    array_push($list_service, [
                        'ship_speed_code' => $item->serviceCode,
                        'description' => isset(DataVietnamPost::codeGTGT[$item->serviceCode]) ? DataVietnamPost::codeGTGT[$item->serviceCode] : $item->serviceCode,
                        'fee' => $item->totalFee,
                    ]);
                }
            }

            return   $list_service;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return json_decode("[]");
        } catch (Exception $e) {

            return json_decode("[]");
        }
    }

    public static function get_list_price_and_type_nhattin_post($shipperArr, $type_ship, $token)
    {
        $config = config('saha.shipper.list_shipper')[4];
        $fee_url = $config["fee_url"];
        $client = new GuzzleClient();

        $account = NhattinPostUtils::handleTokenToAccount($token);

        if (empty($account)) {
            return json_decode("[]");
        }
        $headers = [
            'Content-Type' => 'application/json',
            'username' => $account['username'],
            'password' => $account['password'],
            'partner_id' => $account['partner_id'],
        ];

        $from_province = NhattinPostUtils::getProvinceNhattinPost($account, Place::getNameProvince($shipperArr["from_province_id"]));
        $from_district = NhattinPostUtils::getDistrictNhattinPost($account, $from_province['province_code'], Place::getNameDistrict($shipperArr["from_district_id"]));
        $from_ward = NhattinPostUtils::getWardNhattinPost($account, $from_district['district_code'], Place::getNameWards($shipperArr["from_wards_id"]));

        $to_province = NhattinPostUtils::getProvinceNhattinPost($account, Place::getNameProvince($shipperArr["to_province_id"]));
        $to_district = NhattinPostUtils::getDistrictNhattinPost($account, $to_province['province_code'], Place::getNameDistrict($shipperArr["to_district_id"]));
        $to_ward = NhattinPostUtils::getWardNhattinPost($account, $to_district['district_code'], Place::getNameWards($shipperArr["to_wards_id"]));

        $dataStrip =  [
            "partner_id" => $account['partner_id'],
            "weight" => ($shipperArr["weight"] / 1000) ?? 1,
            "width" => !empty($shipperArr["width"]) ? $shipperArr["width"] : 0,
            "length" => !empty($shipperArr["length"]) ? $shipperArr["length"] : 0,
            "height" => !empty($shipperArr["height"]) ? $shipperArr["height"] : 0,
            "payment_method_id" => 20,
            "cod_amount" => 0,
            "cargo_value" => 0,
            "s_province" => $from_province['province_name'],
            "s_district" => $from_district['district_name'],
            // "s_ward" => $from_ward['ward_name'],
            "r_province" => $to_province['province_name'],
            "r_district" => $to_district['district_name'],
            // "r_ward" => $to_ward['ward_name'],
        ];
        $body = null;
        $bodyTrip = "";

        try {
            try {
                $response = $client->post(
                    $fee_url,
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'json' => $dataStrip,
                    ]
                );

                $body = (string) $response->getBody();
                $bodyTrip  = $body;
            } catch (Exception $e) {
            }

            $list_service = json_decode("[]");

            $jsonResponse = json_decode($body);
            if (!$jsonResponse->success) {
                return json_decode("[]");
            }

            foreach ($jsonResponse->data  as $item) {
                array_push($list_service, [
                    'ship_speed_code' => $item->service_id,
                    'description' => $item->service_name,
                    'fee' => $item->total_fee,
                ]);
            }

            return   $list_service;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return json_decode("[]");
        } catch (Exception $e) {

            return json_decode("[]");
        }
    }

    public static function caculate_monney_vietnam_post($shipperArr, $type_ship, $token)
    {
        $config = config('saha.shipper.list_shipper')[3];
        $fee_url = $config["fee_url"];
        $client = new GuzzleClient();

        $handleToken = ViettelPostUtils::handleToken($token);

        if (empty($handleToken)) {
            return json_decode("[]");
        }

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $handleToken['token'],
        ];

        try {
            $serviceCode = "CTN009"; // mã dịch vụ ship tiêu chuẩn vietnam post
            $from_province = VietnamPostUtils::getProvinceVietnamPost($token, Place::getNameProvince($shipperArr["from_province_id"]));
            $from_district = VietnamPostUtils::getDistrictVietnamPost($token, $from_province['provinceCode'], Place::getNameDistrict($shipperArr["from_district_id"]));
            $from_ward = VietnamPostUtils::getWardVietnamPost($token, $from_district['districtCode'], Place::getNameWards($shipperArr["from_wards_id"]));
            $to_province = VietnamPostUtils::getProvinceVietnamPost($token, Place::getNameProvince($shipperArr["to_province_id"]));
            $to_district = VietnamPostUtils::getDistrictVietnamPost($token, $to_province['provinceCode'], Place::getNameDistrict($shipperArr["to_district_id"]));
            $to_ward = VietnamPostUtils::getWardVietnamPost($token, $to_district['districtCode'], Place::getNameWards($shipperArr["to_wards_id"]));
        } catch (\Throwable $th) {
            return new Exception("null");
        }

        $dataStrip =  [
            "scope" => 1, // 1 vn, 2 quốc tế
            "customerCode" => $handleToken['customerCode'], // mã khách hàng
            "contractCode" => null,
            "data" => [
                "senderProvinceCode" => $from_province['provinceCode'],
                "senderProvinceName" => $from_province['provinceName'],
                "senderDistrictCode" => $from_district['districtCode'],
                "senderDistrictName" => $from_district['districtName'],
                "senderCommuneCode" => $from_ward['communeCode'],
                "senderCommuneName" => $from_ward['communeName'],
                "receiverProvinceCode" => $to_province['provinceCode'],
                "receiverProvinceName" => $to_province['provinceName'],
                "receiverDistrictCode" => $to_district['districtCode'],
                "receiverDistrictName" => $to_district['districtName'],
                "receiverCommuneCode" => $to_ward['communeCode'],
                "receiverCommuneName" => $to_ward['communeName'],
                "receiverName" => $shipperArr['customer_name'] ?? "h",
                "receiverAddress" => $shipperArr['to_address_detail'] ?? ($to_ward['communeName'] . ", " . $to_district['districtName'] . ", " . $to_province['provinceName']),
                "receiverNational" => "VN",
                "receiverCity" => null,
                "orgCodeAccept" => null,
                "receiverPostCode" => null,
                "weight" => $shipperArr["weight"] ?? 0,
                "width" => $shipperArr["width"] ?? 0,
                "length" => $shipperArr["length"] ?? 0,
                "height" => $shipperArr["height"] ?? 0,
                "serviceCode" => $serviceCode,
                "addonService" => [],
                "additionRequest" => [],
                "vehicle" => null
            ]
        ];

        $body = null;
        $bodyTrip = "";
        try {
            try {
                $response = $client->post(
                    "https://connect-my.vnpost.vn/customer-partner/ServicesCharge",
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'json' => $dataStrip,
                    ]
                );
                $body = (string) $response->getBody();
                $bodyTrip  = $body;
            } catch (Exception $e) {
            }

            $body = (string) $response->getBody();

            $jsonResponse = json_decode($body);

            if ($jsonResponse->data == null) {

                return new Exception($jsonResponse->message);
            } else {

                foreach ($jsonResponse  as $item) {
                    if ($item->serviceCode == "CTN009") {
                        return $item->totalFee;
                    }
                }

                return new Exception("null");
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 1)->update(
                            [
                                'use' => false,
                            ]
                        );
                }

                return new Exception($jsonResponse->message);
            }


            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }

    public static function caculate_monney_nhattin($shipperArr, $type_ship, $token)
    {
        $config = config('saha.shipper.list_shipper')[4];
        $fee_url = $config["fee_url"];
        $client = new GuzzleClient();

        $account = NhattinPostUtils::handleTokenToAccount($token);

        if (empty($account)) {
            return json_decode("[]");
        }

        $serviceId = 91; // serviceid giao hàng tiết kiệm của nhattin
        $methodPaymentId = 20; // Receiver pay by Cash when delivery nhattin

        $headers = [
            'Content-Type' => 'application/json',
            'username' => $account['username'],
            'password' => $account['password'],
            'partner_id' => $account['partner_id'],
        ];
        try {
            $from_province = NhattinPostUtils::getProvinceNhattinPost($account, Place::getNameProvince($shipperArr["from_province_id"]));
            $from_district = NhattinPostUtils::getDistrictNhattinPost($account, $from_province['province_code'], Place::getNameDistrict($shipperArr["from_district_id"]));
            $from_ward = NhattinPostUtils::getWardNhattinPost($account, $from_district['district_code'], Place::getNameWards($shipperArr["from_wards_id"]));
            $to_province = NhattinPostUtils::getProvinceNhattinPost($account, Place::getNameProvince($shipperArr["to_province_id"]));
            $to_district = NhattinPostUtils::getDistrictNhattinPost($account, $to_province['province_code'], Place::getNameDistrict($shipperArr["to_district_id"]));
            $to_ward = NhattinPostUtils::getWardNhattinPost($account, $to_district['district_code'], Place::getNameWards($shipperArr["to_wards_id"]));
        } catch (\Throwable $th) {
            return new Exception("null");
        }

        $dataStrip =  [
            "partner_id" => $account['partner_id'],
            "weight" => ($shipperArr["weight"] / 1000) ?? 1,
            "width" => !empty($shipperArr["width"]) ? $shipperArr["width"] : 0,
            "length" => !empty($shipperArr["length"]) ? $shipperArr["length"] : 0,
            "height" => !empty($shipperArr["height"]) ? $shipperArr["height"] : 0,
            "payment_method_id" => $methodPaymentId,
            "service_id" => $serviceId,
            "cod_amount" => 0,
            "cargo_value" => 0,
            "s_province" => $from_province['province_name'],
            "s_district" => $from_district['district_name'],
            "r_province" => $to_province['province_name'],
            "r_district" => $to_district['district_name'],
        ];
        $body = null;
        try {
            try {
                $response = $client->post(
                    $fee_url,
                    [
                        'headers' => $headers,
                        'timeout'         => 15,
                        'connect_timeout' => 15,
                        'json' => $dataStrip,
                    ]
                );

                $body = (string) $response->getBody();
            } catch (Exception $e) {
            }

            $body = (string) $response->getBody();

            $jsonResponse = json_decode($body);

            if (!$jsonResponse->message) {

                return new Exception($jsonResponse->message);
            } else {

                if ($jsonResponse->data == null || !isset($jsonResponse->data[0]->total_fee)) {
                    return new Exception("null");
                }

                return $jsonResponse->data[0]->total_fee;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 1)->update(
                            [
                                'use' => false,
                            ]
                        );
                }

                return new Exception($jsonResponse->message);
            }


            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }
}
