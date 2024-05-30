<?php

namespace App\Models;

use App\Helper\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class BannerAd extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id', 'created_at', 'updated_at'];


    public function getImageUrlAttribute()
    {
        $image_url = empty($this->attributes['image_url']) ? null :  Helper::pathReduceImage($this->attributes['image_url'], 1502, 'webp');
        // $image_url = empty($this->attributes['image_url']) ? null :  strtok($this->attributes['image_url'], '?') . "?new-width=1502&image-type=webp";
        return $image_url;
    }
}
