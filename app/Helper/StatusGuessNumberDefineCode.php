<?php

namespace App\Helper;

class StatusGuessNumberDefineCode
{

    // define apply for 
    const GROUP_CUSTOMER_ALL = 0;
    const GROUP_CUSTOMER_CTV = 1;
    const GROUP_CUSTOMER_AGENCY = 2;
    const GROUP_CUSTOMER_BY_CONDITION = 4;

    // status guess number
    const PROGRESSING = 0;
    const CANCELED = 1;
    const COMPLETED = 2;

    static function defineDataStatusGuessNumber($input_is_num = false)
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

    static function getStatusGuessNumberNum($status, $get_name = false)
    {
        $data = StatusGuessNumberDefineCode::defineDataStatusGuessNumber(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusGuessNumberCode($status, $get_name = false)
    {
        $data = StatusGuessNumberDefineCode::defineDataStatusGuessNumber(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
