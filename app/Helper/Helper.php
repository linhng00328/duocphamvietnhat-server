<?php

namespace App\Helper;

use Carbon\Carbon;
use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class Helper
{

    static function sahaRemoveItemArrayIfNullValue(array $array): array
    {

        $newArray = $array;

        foreach ($newArray as $key => $value) {

            if ($value === null) {
                unset($newArray[$key]);
            }
        }

        return $newArray;
    }


    static function getTimeNowCarbon()
    {
        $mytime = Carbon::now();
        return $mytime;
    }

    static function getStartOfWeek()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek();

        $weekStartDate = $weekStartDate->subDays(1);

        return $weekStartDate;
    }

    static function getStartOfWeekString()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek();
        return Helper::get_begin_date_string($weekStartDate);
    }

    static function startOfMonth()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfMonth();
        return $weekStartDate;
    }

    static function startOfMonthString()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfMonth();
        return Helper::get_begin_date_string($weekStartDate);
    }

    static function startOfYear()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfYear();
        return $weekStartDate;
    }

    static function startOfYearString()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfYear();
        return Helper::get_begin_date_string($weekStartDate);
    }



    static function getTimeNowDateTime()
    {
        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt =  new DateTime($dt->toDateTimeString());
        return $dt;
    }

    static function getTimeNowString()
    {
        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt = $dt->toDateTimeString();
        return $dt;
    }

    static function getRandomOrderString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = $dt1 . $dt2 . Helper::generateRandomString(8);
        return $order_code;
    }

    static function getRandomRevenueExpenditureString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "TC" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }


    static function getRandomTallySheetString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "K" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }

    static function getRandomImportStockString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "N" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }

    static function getRandomTransferStockString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "C" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }

    static public function generateRandomString($length = 8)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static public function generateRandomNum($length = 6)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static public function validEmail($str)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    static public function day_php_to_standard($day)
    {
        $day = (int)$day;
        if ($day == 0) return 8;
        if ($day == 1) return 2;
        if ($day == 2) return 3;
        if ($day == 3) return 4;
        if ($day == 4) return 5;
        if ($day == 5) return 6;
        if ($day == 6) return 7;
        return 8;
    }


    static public function get_begin_date_string($carbonDate)
    {
        $dateFrom = $carbonDate->year . '-' . $carbonDate->month . '-' . $carbonDate->day . ' 00:00:00';
        return $dateFrom;
    }

    static public function get_end_date_string($carbonDate)
    {
        $dateTo = $carbonDate->year . '-' . $carbonDate->month . '-' . $carbonDate->day . ' 23:59:59';
        return $dateTo;
    }

    static function createAndValidateFormatDate($date, $format = 'Y-m')
    {
        try {
            return DateTime::createFromFormat($format, $date);
        } catch (\Throwable $t) {
            return false;
        }
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    static public function checkListImage($images)
    {
        try {
            foreach ($images as $imageItem) {
                if ($imageItem == null || empty($imageItem)) {
                    return false;
                }
            }
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }

    static function hmac_sha256($data, $key)
    {
        return hash_hmac('sha256', $data, $key);
    }
    static function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return $sec . '000';
    }

    static function generateSign($apiName, $params, $secret)
    {
        ksort($params);

        $stringToBeSigned = '';
        $stringToBeSigned .= $apiName;
        foreach ($params as $k => $v) {
            $stringToBeSigned .= "$k$v";
        }
        unset($k, $v);
        return strtoupper(Helper::hmac_sha256($stringToBeSigned, $secret));
    }

    static public function paginateArr($items,  $page = null, $perPage = 20, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    static public function countSortLetters($countLetters)
    {
        $counts = 0;

        if ($countLetters <= 0) {
            return $counts;
        }

        $countLetters = min($countLetters, 26); //A-Za-z
        $counts = self::factorialNumber(26) / (self::factorialNumber(26 - $countLetters) * self::factorialNumber($countLetters));

        return $counts;
    }
    static public function listLettersRandom($countLetters, $countListLetters)
    {
        $list = [];
        while (count($list) < $countListLetters) {
            $string = "";

            for ($j = 0; $j < $countLetters; $j++) {
                $string .= chr(rand(65, 90)); // Mã ASCII từ 65 (A) đến 90 (Z)
            }

            if (!in_array($string, $list)) {
                $list[] = $string;
            }
        }

        return $list;
    }
    static public function factorialNumber($number)
    {
        $factorial = 1;
        for ($i = 1; $i <= $number; $i++) {
            $factorial = $factorial * $i;
        }
        return $factorial;
    }

    static public function isMobile()
    {
        return preg_match("/(Dart|dart|android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    static public function pathReduceImage($originalUrl, $newWidth = 720, $imageType = 'webp')
    {
        // Check if the original URL contains "data3"
        if (strpos($originalUrl, 'data3') === false) {
            return $originalUrl;
        }

        $parsedUrl = parse_url($originalUrl);
        $imageFilename = pathinfo($parsedUrl['path'], PATHINFO_FILENAME);

        // Tạo đường dẫn mới với kích thước và định dạng mong muốn.
        $newPath = sprintf(
            '/images/SHImages/v2/%d/%s/%s.%s',
            $newWidth,
            $imageType,
            $imageFilename,
            $imageType
        );

        // Tạo URL mới từ các phần đã xử lý.
        $newUrl = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$newPath}";

        return $newUrl;
    }
}
