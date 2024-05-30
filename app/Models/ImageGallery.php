<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

use AjCastro\Searchable\Searchable;

class ImageGallery extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];



    protected $searchable = [
        'remi_name',
        'filename'
    ];
}
