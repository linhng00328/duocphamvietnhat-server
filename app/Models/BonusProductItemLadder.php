<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusProductItemLadder extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['bo_product'];

    protected $with = ['product'];

    protected $casts = [];

    public function product()
    {
        return $this->belongsto('App\Models\Product');
    }


    public function getBoProductAttribute()
    {
        return Product::where('id', $this->bo_product_id)->first();
    }
}
