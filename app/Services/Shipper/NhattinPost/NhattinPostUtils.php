<?php

namespace App\Services\Shipper\NhattinPost;

use App\Helper\StringUtils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class NhattinPostUtils
{

    static function handleTokenToAccount($token)
    {
        $account = json_decode(base64_decode($token));

        if (!isset($account->username) || !isset($account->password) || !isset($account->partner_id)) {
            return null;
        }

        return [
            'username' => $account->username,
            'password' => $account->password,
            'partner_id' => $account->partner_id,
        ];
    }

    static function getProvinceNhattinPost($account, $name)
    {
        $config = config('saha.shipper.list_shipper')[4];
        $base_url = $config["url_base"];

        $province_url = $base_url . "/v1/loc/provinces";
        //////
        $client = new Client();
        $header = [
            'username' => $account['username'],
            "password" => $account['password'],
            'partner_id' => $account['partner_id'],
        ];


        try {
            $response = $client->get(
                $province_url,
                [
                    'headers' => $header
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);
            if (!$jsonResponse->success) {
                return new Exception($jsonResponse->message);
            } else {
                $listProvinces = $jsonResponse->data;
                $listProvinces = json_decode(json_encode($listProvinces), true);

                $province_name = StringUtils::convert_name_lowcase($name);
                $province_name = str_replace("thanh pho", "", $province_name);
                $province_name = str_replace("tinh", "", $province_name);
                $province_name = str_replace(" ", "", $province_name);
                $province_name = str_replace("-", "", $province_name);
                $province_name = StringUtils::convert_name_vtp($province_name);

                foreach ($listProvinces as $key => $value) {
                    $procompare = StringUtils::convert_name_lowcase($value['province_name'] ?? "");
                    $procompare = str_replace("thanh pho", "", $procompare);
                    $procompare = str_replace("tinh", "", $procompare);
                    $procompare = str_replace(" ", "", $procompare);
                    $procompare = str_replace("-", "", $procompare);
                    $procompare = StringUtils::convert_name_vtp($procompare);

                    if (substr($procompare, 0, 3)  === "nam" && substr($procompare, 0, 3)  == substr($province_name, 0, 3)) {
                        return [
                            'province_name' => $value['province_name'],
                            'province_code' => $value['id']
                        ];
                    }
                    if ($procompare ==  $province_name) {
                        return [
                            'province_name' => $value['province_name'],
                            'province_code' => $value['id']
                        ];
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

        return null;
    }

    static function getDistrictNhattinPost($account, $provinceID, $districtName)
    {
        $config = config('saha.shipper.list_shipper')[4];
        $base_url = $config["url_base"];

        $province_url = $base_url . "/v1/loc/districts";
        $client = new Client();
        $header = [
            'username' => $account['username'],
            "password" => $account['password'],
            'partner_id' => $account['partner_id'],
            'province_id' => $provinceID
        ];

        try {
            $response = $client->get(
                $province_url,
                [
                    'headers' => $header
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);



            if (!$jsonResponse->success) {
                return new Exception($jsonResponse->message);
            } else {
                $listDistricts = $jsonResponse->data;
                $listDistricts = json_decode(json_encode($listDistricts), true);



                $district_name = StringUtils::convert_name_lowcase($districtName);
                $district_name = str_replace(" ", "", $district_name);
                $district_name = str_replace("h.", "", $district_name);
                $district_name = str_replace("q.", "", $district_name);
                $district_name = str_replace("tx.", "", $district_name);
                $district_name = str_replace("quan", "", $district_name);
                $district_name = str_replace("huyen", "", $district_name);
                $district_name = str_replace("thanhpho", "", $district_name);
                $district_name = str_replace("thixa", "", $district_name);
                $district_name = str_replace("thitran", "", $district_name);
                $district_name = StringUtils::convert_name_vtp($district_name);
                $district_name = str_replace("-", "", $district_name);



                foreach ($listDistricts as $key => $value) {
                    $discompare = StringUtils::convert_name_lowcase($value['district_name']);
                    $discompare = str_replace(" ", "", $discompare);
                    $discompare = str_replace("h.", "", $discompare);
                    $discompare = str_replace("q.", "", $discompare);
                    $discompare = str_replace("tx.", "", $discompare);
                    $discompare = str_replace("quan", "", $discompare);
                    $discompare = str_replace("huyen", "", $discompare);
                    $discompare = str_replace("thanhpho", "", $discompare);
                    $discompare = str_replace("thixa", "", $discompare);
                    $discompare = str_replace("thitran", "", $discompare);
                    $discompare = StringUtils::convert_name_vtp($discompare);
                    $discompare = str_replace("-", "", $discompare);

                    if ($discompare ==  $district_name && $value['province_id'] == $provinceID) {
                        return [
                            'district_name' => $value['district_name'],
                            'district_code' => $value['id']
                        ];
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

        return null;
    }

    static function getWardNhattinPost($account, $district_id, $ward_name)
    {

        $config = config('saha.shipper.list_shipper')[4];
        $base_url = $config["url_base"];
        $ward_url = $base_url . "/v1/loc/wards";

        $header = [
            'username' => $account['username'],
            "password" => $account['password'],
            'partner_id' => $account['partner_id'],
            'district_id' => $district_id
        ];

        $client = new Client();

        try {
            $response = $client->get(
                $ward_url,
                [
                    'headers' => $header
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if (!$jsonResponse->success) {
                return new Exception($jsonResponse->message);
            } else {
                $listProvinces = $jsonResponse->data;
                $listProvinces = json_decode(json_encode($listProvinces), true);

                $ward_name = StringUtils::convert_name_lowcase($ward_name);
                $ward_name = str_replace("phuong", "", $ward_name);
                $ward_name = str_replace("xa", "", $ward_name);
                $ward_name = str_replace("thanhpho", "", $ward_name);
                $ward_name = str_replace("thitran", "", $ward_name);
                $ward_name = str_replace(" ", "", $ward_name);

                foreach ($listProvinces as $key => $value) {
                    $discompare = StringUtils::convert_name_lowcase($value['ward_name']);
                    $discompare = str_replace("thitran", "", $discompare);
                    $discompare = str_replace("phuong", "", $discompare);
                    $discompare = str_replace("xa", "", $discompare);
                    $discompare = str_replace("thanhpho", "", $discompare);
                    $discompare = str_replace(" ", "", $discompare);

                    if ($discompare ==  $ward_name && $district_id == $value['district_id']) {
                        return [
                            'ward_name' => $value['ward_name'],
                            'ward_code' => $value['id']
                        ];
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

        return null;
    }
}
