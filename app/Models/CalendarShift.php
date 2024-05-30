<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class CalendarShift extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'is_add' => 'boolean',
    ];
    
}
