<?php

namespace App\Services\Shipper\ViettelPost;

use App\Helper\StringUtils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ViettelPostUtils
{
    static function handleToken($token)
    {
        $dataHandle = json_decode(base64_decode($token));

        if (!isset($dataHandle->token) || !isset($dataHandle->customerCode)) {
            return null;
        }

        return [
            'token' => $dataHandle->token,
            'customerCode' => $dataHandle->customerCode,
            'contractCode' => isset($dataHandle->contractCode) ? $dataHandle->contractCode : ""
        ];
    }

    static function getIDProvinceViettelPost($name)
    {


        $province_url = "https://partner.viettelpost.vn/v2/categories/listProvinceById?provinceId=";
        //////
        $client = new Client();



        try {
            $response = $client->get(
                $province_url
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);



            if ($jsonResponse->status != 200) {
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

                    if (substr($value['PROVINCE_NAME'], 0, 3)) {
                    }

                    $procompare = StringUtils::convert_name_lowcase($value['PROVINCE_NAME'] ?? "");
                    $procompare = str_replace("thanh pho", "", $procompare);
                    $procompare = str_replace("tinh", "", $procompare);
                    $procompare = str_replace(" ", "", $procompare);
                    $procompare = str_replace("-", "", $procompare);
                    $procompare = StringUtils::convert_name_vtp($procompare);

                    if (substr($procompare, 0, 3)  === "nam" && substr($procompare, 0, 3)  == substr($province_name, 0, 3)) {
                        return $value['PROVINCE_ID'];
                    }
                    if ($procompare ==  $province_name) {
                        return $value['PROVINCE_ID'];
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

    static function getIDDistrictViettelPost($provinceID, $districtName)
    {
        $province_url = "https://partner.viettelpost.vn/v2/categories/listDistrict?provinceId=$provinceID";
        //////
        $client = new Client();



        try {
            $response = $client->get(
                $province_url
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);



            if ($jsonResponse->status != 200) {
                return new Exception($jsonResponse->message);
            } else {
                $listProvinces = $jsonResponse->data;
                $listProvinces = json_decode(json_encode($listProvinces), true);



                $district_name = StringUtils::convert_name_lowcase($districtName);
                $district_name = str_replace(" ", "", $district_name);
                $district_name = str_replace("quan", "", $district_name);
                $district_name = str_replace("huyen", "", $district_name);
                $district_name = str_replace("thanhpho", "", $district_name);
                $district_name = str_replace("thixa", "", $district_name);
                $district_name = str_replace("thitran", "", $district_name);
                $district_name = StringUtils::convert_name_vtp($district_name);
                $district_name = str_replace("-", "", $district_name);



                foreach ($listProvinces as $key => $value) {
                    $discompare = StringUtils::convert_name_lowcase($value['DISTRICT_NAME']);
                    $discompare = str_replace(" ", "", $discompare);
                    $discompare = str_replace("quan", "", $discompare);
                    $discompare = str_replace("huyen", "", $discompare);
                    $discompare = str_replace("thanhpho", "", $discompare);
                    $discompare = str_replace("thixa", "", $discompare);
                    $discompare = str_replace("thitran", "", $discompare);
                    $discompare = StringUtils::convert_name_vtp($discompare);
                    $discompare = str_replace("-", "", $discompare);

                    if ($discompare ==  $district_name) {
                        return $value['DISTRICT_ID'];
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

    static function getIDWardViettelPost($district_id, $ward_name)
    {

        $province_url = "https://partner.viettelpost.vn/v2/categories/listWards?districtId=$district_id";


        //////
        $client = new Client();

        try {
            $response = $client->get(
                $province_url
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->status != 200) {
                return new Exception($jsonResponse->message);
            } else {
                $listProvinces = $jsonResponse->data;
                $listProvinces = json_decode(json_encode($listProvinces), true);

                $district_name = StringUtils::convert_name_lowcase($ward_name);
                $district_name = str_replace("phuong", "", $district_name);
                $district_name = str_replace("xa", "", $district_name);
                $district_name = str_replace("thanhpho", "", $district_name);
                $district_name = str_replace(" ", "", $district_name);


                foreach ($listProvinces as $key => $value) {
                    $discompare = StringUtils::convert_name_lowcase($value['WARDS_NAME']);
                    $discompare = str_replace("phuong", "", $discompare);
                    $discompare = str_replace("xa", "", $discompare);
                    $discompare = str_replace("thanhpho", "", $discompare);
                    $discompare = str_replace(" ", "", $discompare);




                    if ($discompare ==  $district_name) {
                        return $value['WARDS_ID'];
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
