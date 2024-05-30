<?php

namespace App\Models;

use App\Helper\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class PopupCustomer extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'show_once' => 'boolean',
    ];

    protected $hidden = ['store_id', "created_at", "updated_at"];

    public function getLinkImageAttribute()
    {
        $link_image = empty($this->attributes['link_image']) ? null :  Helper::pathReduceImage($this->attributes['link_image'], 752, 'webp');
        // $link_image = empty($this->attributes['link_image']) ? null :  strtok($this->attributes['link_image'],'?') . "?new-width=752&image-type=webp";
        return $link_image;
    }
}
