<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuessNumberResult extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_correct' => 'boolean'
    ];
}
