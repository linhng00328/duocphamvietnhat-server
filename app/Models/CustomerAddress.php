<?php

namespace App\Models;

use App\Helper\Place;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class CustomerAddress extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'is_default' => 'boolean',
    ];


    protected $appends = ['province_name' , 'district_name' , 'wards_name'];

    public function getProvinceNameAttribute()
    {
        return Place::getNameProvince($this->province);
    }

    public function getDistrictNameAttribute()
    {
        return Place::getNameDistrict($this->district);
    }

    public function getWardsNameAttribute()
    {
        return Place::getNameWards($this->wards);
    }
}
