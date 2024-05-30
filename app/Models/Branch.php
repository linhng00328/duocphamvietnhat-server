<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
class Branch extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    
    protected $casts = [
        'is_default' => 'boolean',
        'is_default_order_online' => 'boolean'
    ];
}
