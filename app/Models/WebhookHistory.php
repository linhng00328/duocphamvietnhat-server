<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class WebhookHistory extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'has_update' => 'boolean'
    ];

    // type 
    const TYPE_VIETTEL_POST = 0;
    const TYPE_VIETNAM_POST = 1;
    const TYPE_NHATTIN_POST = 2;
}
