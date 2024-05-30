<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerAdApp extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id', 'created_at','updated_at'];

    protected $casts = [
        'is_show' => 'boolean'
    ];
}
