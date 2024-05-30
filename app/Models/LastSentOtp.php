<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use Nicolaslopezj\Searchable\SearchableTrait;

class LastSentOtp extends BaseModel
{
    use HasFactory;
    // use SearchableTrait;

    protected $guarded = [];

    // protected $searchable = [
    //     'columns' => [
    //         'last_sent_otps.phone' => 10,
    //         'last_sent_otps.phone' => 10,
    //         'last_sent_otps.content' => 10,
    //     ],
    // ];


    // Type otp
    const TYPE_AUTH = 0;
    const TYPE_ORDER = 1;
}
