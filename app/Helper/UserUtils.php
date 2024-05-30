<?php

namespace App\Helper;

use App\Models\User;

class UserUtils
{

    static function isUser()
    {
        $user = request('user', $default = null);
        $staff = request('staff', $default = null);
        if ($user != null || $staff != null) {
            return true;
        }
        return false;
    }
}
