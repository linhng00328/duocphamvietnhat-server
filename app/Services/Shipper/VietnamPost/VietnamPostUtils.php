<?php

namespace App\Services\Shipper\VietnamPost;

use App\Helper\StringUtils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use App\Services\Shipper\VietnamPost\DataVietnamPost;

class VietnamPostUtils
{

    static function getProvinceVietnamPost($token = null, $name)
    {


        $province_url = "https://my-uat.vnpost.vn/MYVNP_API/customer-partner/getAllProvince";
        //////
        $client = new Client();


        try {
            // $response = $client->get(
            //     $province_url,
            //     [
            //         'headers' => [
            //             'token' => $token,
            //         ],
            //     ]
            // );

            // $body = (string) $response->getBody();
            // $jsonResponse = json_decode($body);
            $jsonResponse = DataVietnamPost::province;

            if (empty($jsonResponse)) {
                return new Exception("Error fetch data Vietnam post");
            } else {
                $listProvinces = $jsonResponse;
                $listProvinces = json_decode(json_encode($listProvinces), true);
                $province_name = StringUtils::convert_name_lowcase($name);
                $province_name = str_replace("thanh pho", "", $province_name);
                $province_name = str_replace("tinh", "", $province_name);
                $province_name = str_replace(" ", "", $province_name);
                $province_name = str_replace("-", "", $province_name);
                $province_name = StringUtils::convert_name_vtp($province_name);

                foreach ($listProvinces as $key => $value) {

                    if (substr($value['provinceName'], 0, 3)) {
                    }

                    $procompare = StringUtils::convert_name_lowcase($value['provinceName'] ?? "");
                    $procompare = str_replace("thanh pho", "", $procompare);
                    $procompare = str_replace("tinh", "", $procompare);
                    $procompare = str_replace(" ", "", $procompare);
                    $procompare = str_replace("-", "", $procompare);
                    $procompare = str_replace("tp.", "", $procompare);
                    $procompare = StringUtils::convert_name_vtp($procompare);

                    if (substr($procompare, 0, 3)  === "nam" && substr($procompare, 0, 3)  == substr($province_name, 0, 3)) {
                        return $value;
                    }
                    if ($procompare ==  $province_name) {
                        return $value;
                    }
                }

                return null;
            }
        } catch (Exception $e) {
            return new Exception('error');
        }

        return null;
    }

    static function getDistrictVietnamPost($token = null, $provinceCode, $districtName)
    {
        $province_url = "https://my-uat.vnpost.vn/MYVNP_API/customer-partner/getAllDistrict";
        $client = new Client();
        try {
            // $response = $client->get(
            //     $province_url,
            //     [
            //         'headers' => [
            //             'token' => $token,
            //         ],
            //     ]
            // );

            // $body = (string) $response->getBody();
            // $jsonResponse = json_decode($body);
            $jsonResponse = DataVietnamPost::district;

            if (empty($jsonResponse)) {
                return new Exception("Error fetch data district Vietnam post");
            } else {
                $listDistricts = $jsonResponse;
                $listDistricts = json_decode(json_encode($listDistricts), true);

                $district_name = StringUtils::convert_name_lowcase($districtName);
                $district_name = str_replace(" ", "", $district_name);
                $district_name = str_replace("quan", "", $district_name);
                $district_name = str_replace("huyen", "", $district_name);
                $district_name = str_replace("thanhpho", "", $district_name);
                $district_name = str_replace("thixa", "", $district_name);
                $district_name = str_replace("thitran", "", $district_name);
                $district_name = str_replace("h.", "", $district_name);
                $district_name = str_replace("tp.", "", $district_name);
                $district_name = str_replace("q.", "", $district_name);
                $district_name = str_replace("tx.", "", $district_name);
                $district_name = StringUtils::convert_name_vtp($district_name);
                $district_name = str_replace("-", "", $district_name);



                foreach ($listDistricts as $key => $value) {

                    if ($provinceCode == $value['provinceCode']) {
                        $discompare = StringUtils::convert_name_lowcase($value['districtName']);
                        $discompare = str_replace(" ", "", $discompare);
                        $discompare = str_replace("quan", "", $discompare);
                        $discompare = str_replace("huyen", "", $discompare);
                        $discompare = str_replace("thanhpho", "", $discompare);
                        $discompare = str_replace("thixa", "", $discompare);
                        $discompare = str_replace("thitran", "", $discompare);
                        $discompare = str_replace("h.", "", $discompare);
                        $discompare = str_replace("tp.", "", $discompare);
                        $discompare = str_replace("q.", "", $discompare);
                        $discompare = str_replace("tx.", "", $discompare);
                        $discompare = StringUtils::convert_name_vtp($discompare);
                        $discompare = str_replace("-", "", $discompare);
                        if ($discompare == $district_name) {
                            return $value;
                        }
                    }
                }

                return null;
            }
        } catch (Exception $e) {

            return new Exception('error');
        }

        return null;
    }

    static function getWardVietnamPost($token = null, $districtCode, $ward_name)
    {

        $province_url = "https://my-uat.vnpost.vn/MYVNP_API/categories/listWards?districtId=$districtCode";

        //////
        $client = new Client();

        // try {
        // $response = $client->get(
        //     $province_url
        // );

        // $body = (string) $response->getBody();
        // $jsonResponse = json_decode($body);
        $jsonResponse = DataVietnamPost::ward;


        if (empty($jsonResponse)) {
            return new Exception("Error fetch data Vietnam post");
        } else {
            $listWards = $jsonResponse;
            $listWards = json_decode(json_encode($listWards), true);
            $ward_name = StringUtils::convert_name_lowcase($ward_name);
            $ward_name = mb_convert_encoding($ward_name, "UTF-8");
            $ward_name = htmlspecialchars($ward_name, ENT_QUOTES, "UTF-8");
            $ward_name = str_replace("phuong", "", $ward_name);
            $ward_name = str_replace(" ", "", $ward_name);
            $ward_name = str_replace("xa", "", $ward_name);
            $ward_name = str_replace("thanhpho", "", $ward_name);
            $ward_name = str_replace("x.", "", $ward_name);
            $ward_name = str_replace("thitran", "", $ward_name);
            $ward_name = str_replace("tt.", "", $ward_name);
            $ward_name = str_replace("p.", "", $ward_name);

            foreach ($listWards as $key => $value) {
                if ($districtCode == $value['districtCode']) {
                    $discompare = StringUtils::convert_name_lowcase($value['communeName']);
                    $discompare = mb_convert_encoding($discompare, "UTF-8");
                    $discompare = htmlspecialchars($discompare, ENT_QUOTES, "UTF-8");
                    $discompare = str_replace("phuong", "", $discompare);
                    $discompare = str_replace("d", "d", $discompare);
                    $discompare = str_replace(" ", "", $discompare);
                    $discompare = str_replace("xa", "", $discompare);
                    $discompare = str_replace("thanhpho", "", $discompare);
                    $discompare = str_replace("x.", "", $discompare);
                    $discompare = str_replace("thitran", "", $discompare);
                    $discompare = str_replace("tt.", "", $discompare);
                    $discompare = str_replace("p.", "", $discompare);
                    if ($discompare ==  $ward_name || strpos($discompare, $ward_name) !== false) {
                        return $value;
                    }
                }
            }

            return null;
        }
        // } catch (Exception $e) {

        //     return new Exception('error');
        // }

        return null;
    }
}
