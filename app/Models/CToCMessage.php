<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CToCMessage extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'images'
    ];

    protected $casts = [
        'vs_customer_id' => "integer",
        'is_sender' => 'boolean'
    ];
    protected $hidden = ['images_json'];


    public function getImagesAttribute()
    {
        return json_decode($this->images_json);
    }
}
