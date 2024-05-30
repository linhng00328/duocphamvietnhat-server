<?php

namespace App\Helper;

class StatusHistorySpinWheelDefineCode
{
    // type_from - history turn receive
    const TURN_PER_DAY = 0;
    const TURN_ORDER = 1;
    const TURN_MISSION = 2;
    const TURN_RANK = 3;

    static function defineDataHistoryTypeTurnSpinWheel($input_is_num = false)
    {

        if ($input_is_num == false) {
            $data = [
                "TURN_PER_DAY" => [0, "TURN_PER_DAY", "Lượt chơi mỗi ngày"],
                "TURN_ORDER" => [1, "TURN_ORDER", "Lượt chơi từ đặt hàng"],
                "TURN_MISSION" => [2, "TURN_MISSION", "Lượt chơi từ nhiệm vụ"],
                "TURN_RANK" => [3, "TURN_RANK", "Lượt chơi từ xếp hạng"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "TURN_PER_DAY", "Lượt chơi mỗi ngày"],
                1 => [1, "TURN_ORDER", "Lượt chơi từ đặt hàng"],
                2 => [2, "TURN_MISSION", "Lượt chơi từ nhiệm vụ"],
                3 => [3, "TURN_RANK", "Lượt chơi từ xếp hạng"]
            ];
            return $data;
        }
    }

    static function getHistoryTypeTurnSpinWheelNum($status, $get_name = false)
    {
        $data = StatusHistorySpinWheelDefineCode::defineDataHistoryTypeTurnSpinWheel(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getHistoryTypeTurnSpinWheelCode($status, $get_name = false)
    {
        $data = StatusHistorySpinWheelDefineCode::defineDataHistoryTypeTurnSpinWheel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
