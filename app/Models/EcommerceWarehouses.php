<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceWarehouses extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'allow_sync' => 'boolean'
    ];
}
