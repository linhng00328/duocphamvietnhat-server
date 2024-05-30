<?php

namespace App\Models;

use App\Helper\Place;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ListCart extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_use_points' => 'boolean',
        'is_use_balance_collaborator' => 'boolean',
        'is_default' => 'boolean'
    ];
}
