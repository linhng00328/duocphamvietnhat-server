<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class AgencyConfig extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'type_rose' => 'integer',
        'allow_payment_request' => 'boolean',
        'payment_1_of_month' => 'boolean',
        'payment_16_of_month' => 'boolean',
        'allow_rose_referral_customer' => 'boolean',
        'setting_for_all_products' => 'boolean',
        'auto_set_level_agency' => 'boolean',
    ];
}
