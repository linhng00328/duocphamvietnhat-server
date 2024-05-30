<?php

namespace App\Helper;

use Illuminate\Http\Request;

class IPUtils
{

    static function getIP() //chuẩn hóa
    {

        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }


    static function getIPv4FromIPv6($ipv6)
    {
        $ipv6Parts = explode(':', $ipv6);
        $ipv4Parts = array_slice($ipv6Parts, -4);
        $ipv4 = implode('.', $ipv4Parts);

        return $ipv4;
    }

    static public function getIPv4(Request $request)
    {


        $ipv4 = $request->header('CF-Connecting-IP');
        if (!empty($ipv4)) {
            return $ipv4;
        }
        return IPUtils::getIP();
    }
}
