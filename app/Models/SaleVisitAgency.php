<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleVisitAgency extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_agency_open' => 'boolean',
    ];

    function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }
}
