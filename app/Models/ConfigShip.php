<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigShip extends Model
{

    const is_calculate_ship = false;
    const use_fee_from_partnership = false;
    const use_fee_from_default = false;
    const fee_urban = 0;
    const fee_suburban = 0;

    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_calculate_ship' => 'boolean',
        'use_fee_from_partnership' => 'boolean',
        'use_fee_from_default' => 'boolean',
    ];


    protected $appends = ['urban_list_id_province','urban_list_name_province'];

 
     public function getUrbanListIdProvinceAttribute()
     {
       
         return json_decode($this->urban_list_id_province_json);
     }

     public function getUrbanListNameProvinceAttribute()
     {
       
         return json_decode($this->urban_list_name_province_json);
     }
}
