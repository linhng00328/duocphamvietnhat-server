<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class PointSetting extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_set_order_max_point' => 'boolean',
        'allow_use_point_order' => 'boolean',
        'is_percent_use_max_point' => 'boolean',
        'bonus_point_product_to_agency' => 'boolean',
        'bonus_point_bonus_product_to_agency' => 'boolean',
        
    ];
}
