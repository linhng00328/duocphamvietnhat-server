<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicLink extends Model
{
    use HasFactory;
    protected $casts = [
        'handled' => 'boolean',
    ];
}
