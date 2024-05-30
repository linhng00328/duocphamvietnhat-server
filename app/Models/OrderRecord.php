<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class OrderRecord extends BaseModel
{
    use HasFactory;
    protected $guarded = [];


    protected $casts = [
        'customer_cant_see' => 'boolean',
    ];
    protected $hidden = [
        'store_id',
        'customer_id',
    ];
}
