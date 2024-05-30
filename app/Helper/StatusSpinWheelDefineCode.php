<?php

namespace App\Helper;

class StatusSpinWheelDefineCode
{

    // define apply for 
    const GROUP_CUSTOMER_ALL = 0;
    const GROUP_CUSTOMER_CTV = 1;
    const GROUP_CUSTOMER_AGENCY = 2;
    const GROUP_CUSTOMER_BY_CONDITION = 4;

    // status spin wheel
    const PROGRESSING = 0;
    const CANCELED = 1;
    const COMPLETED = 2;

    // type gift
    const GIFT_IS_COIN = 0;
    const GIFT_IS_ITEM = 1;
    const GIFT_IS_TEXT = 2;
    const GIFT_IS_LUCKY_AFTER = 3;
    const GIFT_IS_LOST_TURN = 4;

    static function defineDataStatusSpinWheel($input_is_num = false)
    {

        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Chờ xử lý"],
                "CANCELED" => [1, "CANCELED", "Huỷ"],
                "COMPLETED" => [2, "COMPLETED", "Hoàn tất"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Chờ xử lý"],
                1 => [1, "CANCELED", "Huỷ"],
                2 => [2, "COMPLETED", "Hoàn tất"]
            ];
            return $data;
        }
    }

    static function getStatusSpinWheelNum($status, $get_name = false)
    {
        $data = StatusSpinWheelDefineCode::defineDataStatusSpinWheel(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusSpinWheelCode($status, $get_name = false)
    {
        $data = StatusSpinWheelDefineCode::defineDataStatusSpinWheel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }

    static function defineDataTypeGiftSpinWheel($input_is_num = false)
    {

        if ($input_is_num == false) {
            $data = [
                "GIFT_IS_COIN" => [0, "GIFT_IS_COIN", "Phần thưởng là xu"],
                "GIFT_IS_ITEM" => [1, "GIFT_IS_ITEM", "Phần thưởng là sản phẩm"],
                "GIFT_IS_TEXT" => [2, "GIFT_IS_TEXT", "Phần thưởng là bất kì"],
                "GIFT_IS_LUCKY_AFTER" => [3, "GIFT_IS_LUCKY_AFTER", "Chúc bạn may mắn lần sau"],
                "GIFT_IS_LOST_TURN" => [4, "GIFT_IS_LOST_TURN", "Mất lượt"]
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "GIFT_IS_COIN", "Phần thưởng "],
                1 => [1, "GIFT_IS_ITEM", "Phần thưởng là sản phẩm"],
                2 => [2, "GIFT_IS_TEXT", "Phần thưởng là bất kì"],
                3 => [3, "GIFT_IS_LUCKY_AFTER", "Chúc bạn may mắn lần sau"],
                4 => [4, "GIFT_IS_LOST_TURN", "Mất lượt"]
            ];
            return $data;
        }
    }

    static function getTypeGiftSpinWheelNum($status, $get_name = false)
    {
        $data = StatusSpinWheelDefineCode::defineDataTypeGiftSpinWheel(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getTypeGiftSpinWheelCode($status, $get_name = false)
    {
        $data = StatusSpinWheelDefineCode::defineDataTypeGiftSpinWheel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }

    static function defineDataSpinWheelApplyFor($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "GROUP_CUSTOMER_ALL" => [0, "GROUP_CUSTOMER_ALL", "Tất cả nhóm khách hàng"],
                "GROUP_CUSTOMER_CTV" => [1, "GROUP_CUSTOMER_CTV", "Nhóm cộng tác viên"],
                "GROUP_CUSTOMER_AGENCY" => [2, "GROUP_CUSTOMER_AGENCY", "Nhóm đại lý"],
                "GROUP_CUSTOMER_BY_CONDITION" => [4, "GROUP_CUSTOMER_BY_CONDITION", "Nhóm khách có điều kiện"]
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "GROUP_CUSTOMER_ALL", "Tất cả nhóm khách hàng"],
                1 => [1, "GROUP_CUSTOMER_CTV", "Nhóm cộng tác viên"],
                2 => [2, "GROUP_CUSTOMER_AGENCY", "Nhóm đại lý"],
                4 => [4, "GROUP_CUSTOMER_BY_CONDITION", "Nhóm khách có điều kiện"]
            ];
            return $data;
        }
    }

    static function getSpinWheelApplyForNum($status, $get_name = false)
    {
        $data = StatusSpinWheelDefineCode::defineDataSpinWheelApplyFor(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getSpinWheelApplyForCode($status, $get_name = false)
    {
        $data = StatusSpinWheelDefineCode::defineDataSpinWheelApplyFor(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
