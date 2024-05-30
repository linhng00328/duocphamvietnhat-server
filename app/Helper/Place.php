<?php

namespace App\Helper;

use App\Helper\PlaceJsonSpeed;

class Place
{

    static function handleFile()  //Hiện tại ko lưu trong Storage
    {
        // $contents = Storage::get('location/vn/vn.json');


        //$jsonFile = json_decode($contents, true);


        $jsonFile = PlaceJsonSpeed::jsonSpeed();

        return $jsonFile;
    }

    static function getIDProvinceFromName($name)
    {
        $jsonFile = PlaceJsonSpeed::jsonSpeed();

        $name = StringUtils::convert_name_lowcase($name);
        $name  = str_replace("thanh pho", "", $name);
        $name = str_replace("tinh", "",  $name);
        $name  = str_replace(" ", "", $name);
        $name  = str_replace("-", "", $name);
        $name = StringUtils::convert_name_vtp($name);

        $listProvinces = $jsonFile["data"];

        $listProvinces = array_map(function ($value) {
            $value["name"] = StringUtils::convert_name_lowcase($value["name"]);
            $value["name"] = str_replace("thanh pho", "", $value["name"]);
            $value["name"] = str_replace("tinh", "", $value["name"]);
            $value["name"] = str_replace(" ", "", $value["name"]);
            $value["name"] = str_replace("-", "", $value["name"]);
            $value["name"] = StringUtils::convert_name_vtp($value["name"]);

            return $value;
        }, $listProvinces);


        foreach ($listProvinces as $key => $value) {

            if ($value["name"] === $name) {

                return (int)$value["level1_id"];
            }
        }

        return null;
    }

    static function getIDDistrictFromName($provinceID, $districtName)
    {
        $jsonFile = PlaceJsonSpeed::jsonSpeed();

        $districtName = StringUtils::convert_name_lowcase($districtName);
        $districtName  = str_replace("thanh pho", "", $districtName);
        $districtName  = str_replace("quan", "", $districtName);
        $districtName = str_replace("huyen", "",  $districtName);
        $districtName  = str_replace(" ", "", $districtName);
        $districtName  = str_replace("-", "", $districtName);
        $districtName = StringUtils::convert_name_vtp($districtName);

        $listProvinces = $jsonFile["data"];

        foreach ($listProvinces as $key => $value) {

            if ((int)$value['level1_id'] === $provinceID) {
                foreach ($value['level2s'] as $key => $value) {

                    $value["name"] = StringUtils::convert_name_lowcase($value["name"]);
                    $value["name"] = str_replace("thanh pho", "", $value["name"]);
                    $value["name"] = str_replace("quan", "", $value["name"]);
                    $value["name"] = str_replace("huyen", "", $value["name"]);
                    $value["name"] = str_replace(" ", "", $value["name"]);
                    $value["name"] = str_replace("-", "", $value["name"]);
                    $value["name"] = StringUtils::convert_name_vtp($value["name"]);


                    if ($value["name"] === $districtName) {
                        return (int)$value["level2_id"];
                    }
                }
            }
        }

        return null;
    }

    static function getIDWardsFromName($provinceID, $districtID, $wardsName)
    {
        $jsonFile = PlaceJsonSpeed::jsonSpeed();

        $wardsName = StringUtils::convert_name_lowcase($wardsName);
        $wardsName  = str_replace("xa", "", $wardsName);
        $wardsName  = str_replace("thi tran", "", $wardsName);
        $wardsName = str_replace("phuong", "",  $wardsName);
        $wardsName  = str_replace(" ", "", $wardsName);
        $wardsName  = str_replace("-", "", $wardsName);
        $wardsName = StringUtils::convert_name_vtp($wardsName);

        $listProvinces = $jsonFile["data"];

        foreach ($listProvinces as $key => $valueProvinces) {

            if ((int)$valueProvinces['level1_id'] === $provinceID) {

                foreach ($valueProvinces['level2s'] as $key => $valueDistrict) {

                    if ((int)$valueDistrict["level2_id"] === $districtID) {
                        foreach ($valueDistrict['level3s'] as $key => $value) {

                            $value["name"] = StringUtils::convert_name_lowcase($value["name"]);
                            $value["name"] = str_replace("xa", "", $value["name"]);
                            $value["name"] = str_replace("thi tran", "", $value["name"]);
                            $value["name"] = str_replace("phuong", "", $value["name"]);
                            $value["name"] = str_replace(" ", "", $value["name"]);
                            $value["name"] = str_replace("-", "", $value["name"]);
                            $value["name"] = StringUtils::convert_name_vtp($value["name"]);

                            if ($value["name"] === $wardsName) {

                                return (int)$value["level3_id"];
                            }
                        }
                    }
                }
            }
        }

        return null;
    }


    static function getNameProvince($id)
    {

        $data = null;
        $id = (int)$id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            if ((int)$value["level1_id"] == $id) {

                return $value["name"];
            }
        }



        return $data;
    }

    static function getNameDistrict($id)
    {
        $data = null;
        $id = (int)$id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            foreach ($value["level2s"] as $key => $value2) {
                if ((int)$value2["level2_id"] == $id) {
                    return $value2["name"];
                }
            }
        }

        return $data;
    }

    static function getNameWards($id)
    {
        $data = null;
        $id = (int)$id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            foreach ($value["level2s"] as $key => $value2) {
                foreach ($value2["level3s"] as $key => $value3) {
                    if ((int)$value3["level3_id"] == $id) {
                        return $value3["name"];
                    }
                }
            }
        }

        return $data;
    }

    static function getListProvince($parent_id)
    {
        $data = array();
        $parent_id = (int)$parent_id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            array_push($data, [
                "id" => (int)$value["level1_id"],
                "name" => $value["name"],
                "type" => $value["type"],
            ]);
        }

        return $data;
    }

    static function getListDistrict($parent_id)
    {
        $data = array();
        $parent_id = (int)$parent_id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            if ((int)$value["level1_id"] == $parent_id) {
                foreach ($value["level2s"] as $key => $value2) {
                    array_push($data, [
                        "id" => (int)$value2["level2_id"],
                        "name" => $value2["name"],
                        "type" => $value2["type"],
                    ]);
                }
            }
        }

        return $data;
    }

    static function getListWards($parent_id)
    {
        $data = array();
        $parent_id = (int)$parent_id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            foreach ($value["level2s"] as $key => $value2) {
                if ((int)$value2["level2_id"] == $parent_id) {
                    foreach ($value2["level3s"] as $key => $value3) {
                        array_push($data, [
                            "id" => (int)$value3["level3_id"],
                            "name" => $value3["name"],
                            "type" => $value3["type"],
                        ]);
                    }
                }
            }
        }

        return $data;
    }
}
