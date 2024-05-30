<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Shipment extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = ['id', 'store_id'];

    protected $casts = [
        'use' => 'boolean',
        'cod' => 'boolean',
    ];
}
