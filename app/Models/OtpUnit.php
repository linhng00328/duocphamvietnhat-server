<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpUnit extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_use' => 'boolean',
        'is_default' => 'boolean',
        'is_order' => 'boolean'
    ];

    const SPEED_SMS = 'SPEED_SMS';
}
