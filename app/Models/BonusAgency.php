<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class BonusAgency extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id','id'];
    protected $casts = [
        'is_end' => 'boolean',
    ];
}
