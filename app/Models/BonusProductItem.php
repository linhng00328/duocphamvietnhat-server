<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusProductItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $with = ['product'];

    protected $casts = [
        'allows_choose_distribute' => 'boolean',
        'allows_all_distribute' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsto('App\Models\Product');
    }
}
