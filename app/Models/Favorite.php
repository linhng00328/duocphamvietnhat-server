<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Favorite extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(
            'App\Models\Product',
        );
    }
}
