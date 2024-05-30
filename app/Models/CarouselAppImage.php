<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class CarouselAppImage extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    public function appTheme()
    {
        return $this->belongsto('App\Models\AppTheme');
    }


}
