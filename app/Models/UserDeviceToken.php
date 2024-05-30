<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class UserDeviceToken extends BaseModel
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function stores()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
