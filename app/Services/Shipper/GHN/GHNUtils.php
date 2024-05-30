<?php

namespace App\Services\Shipper\GHN;

use App\Helper\StringUtils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class GHNUtils
{


    static function getIDProvinceGHN($name)
    {
        $contents = Storage::get('location/ghn/province.json');
        $jsonFile = json_decode($contents, true);

        $name = StringUtils::convert_name_lowcase($name);
        $name  = str_replace("thanh pho", "", $name);
        $name = str_replace("tinh", "",  $name);
        $name  = str_replace(" ", "", $name);
        $name  = str_replace("-", "", $name);
        $name = StringUtils::convert_name_vtp($name);

        $listProvinces = $jsonFile["data"];

        $listProvinces = array_map(function ($value) {
            $value["ProvinceName"] = StringUtils::convert_name_lowcase($value["ProvinceName"]);
            $value["ProvinceName"] = str_replace("thanh pho", "", $value["ProvinceName"]);
            $value["ProvinceName"] = str_replace("tinh", "", $value["ProvinceName"]);
            $value["ProvinceName"] = str_replace(" ", "", $value["ProvinceName"]);
            $value["ProvinceName"] = str_replace("-", "", $value["ProvinceName"]);
            $value["ProvinceName"] = StringUtils::convert_name_vtp($value["ProvinceName"]);

            if (array_key_exists("NameExtension", $value) && count($value["NameExtension"]) > 0) {

                $listExtension = array_map(function ($valueCompare) {
                    $valueCompare = StringUtils::convert_name_lowcase($valueCompare);
                    $valueCompare = str_replace("thanh pho", "", $valueCompare);
                    $valueCompare = str_replace("tinh", "", $valueCompare);
                    $valueCompare = str_replace(" ", "", $valueCompare);
                    $valueCompare = str_replace("-", "", $valueCompare);
                    $valueCompare = StringUtils::convert_name_vtp($valueCompare);

                    return $valueCompare;
                }, $value["NameExtension"]);

                $value["NameExtension"] = $listExtension;
            }


            return $value;
        }, $listProvinces);


        foreach ($listProvinces as $key => $value) {


            $value["ProvinceName"] = StringUtils::convert_name_lowcase($value["ProvinceName"]);
            $value["ProvinceName"] = str_replace("thanh pho", "", $value["ProvinceName"]);
            $value["ProvinceName"] = str_replace("tinh", "", $value["ProvinceName"]);
            $value["ProvinceName"] = str_replace(" ", "", $value["ProvinceName"]);
            $value["ProvinceName"] = str_replace("-", "", $value["ProvinceName"]);
            $value["ProvinceName"] = StringUtils::convert_name_vtp($value["ProvinceName"]);


            if ($value["ProvinceName"] == $name || in_array($name, $value["NameExtension"]) == true) {
                return $value["ProvinceID"];
            }
        }

        return null;
    }

    static function getIDDistrictGHN($provinceID, $districtName)
    {
        $contents = Storage::get('location/ghn/district.json');
        $jsonFile = json_decode($contents, true);


        $districtName = StringUtils::convert_name_lowcase($districtName);

        $listDistricts = $jsonFile["data"];
        $listDistricts = array_map(function ($value) {
            $value["DistrictName"] = StringUtils::convert_name_lowcase($value["DistrictName"]);


            if (array_key_exists("NameExtension", $value) && count($value["NameExtension"]) > 0) {
                $listExtension = array_map(function ($value2) {
                    return StringUtils::convert_name_lowcase($value2);
                }, $value["NameExtension"]);

                $value["NameExtension"] = $listExtension;
            }



            return $value;
        }, $listDistricts);



        foreach ($listDistricts as $key => $value) {
            if ($value["ProvinceID"] == $provinceID && ($value["DistrictName"] == $districtName ||
                isset($value["NameExtension"]) &&  in_array($districtName, $value["NameExtension"]))) {
                return $value["DistrictID"];
            }
        }

        return null;
    }


    static function getWardCodeGHN($token, $district_id, $ward_name)
    {

        function removeZero($str)
        {
            $str = str_replace("09", "9", $str);
            $str = str_replace("08", "8", $str);
            $str = str_replace("07", "7", $str);
            $str = str_replace("06", "6", $str);
            $str = str_replace("05", "5", $str);
            $str = str_replace("04", "4", $str);
            $str = str_replace("03", "3", $str);
            $str = str_replace("02", "2", $str);
            $str = str_replace("01", "1", $str);
            $str = str_replace("00", "0", $str);
            return $str;
        }

        $config = config('saha.shipper.list_shipper')[0];
        $ward_url = "https://online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id=$district_id";
        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $ward_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' =>  [
                        "district_id" => $district_id
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->code != 200) {
                return new Exception($jsonResponse->message);
            } else {


                $listWards = $jsonResponse->data;
                $listWards = json_decode(json_encode($listWards), true);


                $listWards = array_map(function ($value) {

                    $value["WardName"] = StringUtils::convert_name_lowcase($value["WardName"]);
                    $value["WardName"] =  removeZero($value["WardName"]);
                    $value["WardName"] =  str_replace('xa', "", $value["WardName"]);
                    $value["WardName"] =  str_replace('thi tran', "", $value["WardName"]);
                    $value["WardName"] =  str_replace('phuong', "", $value["WardName"]);
                    $value["WardName"] =  str_replace(' ', "", $value["WardName"]);

                    if (array_key_exists("NameExtension", $value) && count($value["NameExtension"]) > 0) {
                        $listExtension = array_map(function ($value2) {
                            return StringUtils::convert_name_lowcase($value2);
                        }, $value["NameExtension"]);

                        $value["NameExtension"] = $listExtension;
                    }



                    return $value;
                }, $listWards);


                $ward_name = StringUtils::convert_name_lowcase($ward_name);
                $ward_name =  str_replace('xa', "", $ward_name);
                $ward_name =  str_replace('thi tran', "", $ward_name);
                $ward_name =  str_replace('phuong', "", $ward_name);
                $ward_name =  str_replace(' ', "", $ward_name);

                foreach ($listWards as $key => $value) {

                    $ward_name = removeZero($ward_name);
                    if ($value["DistrictID"] == $district_id && ($value["WardName"] == $ward_name ||
                        isset($value["NameExtension"]) &&  in_array($ward_name, $value["NameExtension"]))) {
                        return $value["WardCode"];
                    }
                }

                return null;
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
