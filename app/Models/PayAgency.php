<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class PayAgency extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'agency',
    ];

    public function agency()
    {
        return $this->belongsto('App\Models\Agency');
    }

}
