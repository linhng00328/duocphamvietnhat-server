<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class CustomerDeviceToken extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'active' => 'boolean',
    ];
}
