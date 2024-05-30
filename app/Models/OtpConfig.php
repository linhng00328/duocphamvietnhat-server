<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpConfig extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_use' => 'boolean',
        'is_use_from_default' => 'boolean',
        'is_use_from_units' => 'boolean',
    ];
}
