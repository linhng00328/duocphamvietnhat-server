<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerGuessNumber extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_get_turn_today' => 'boolean'
    ];
}
