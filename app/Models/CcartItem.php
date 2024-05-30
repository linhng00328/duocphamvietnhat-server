<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use App\Traits\CcartItemTrait;

class CcartItem extends BaseModel
{
    use HasFactory;
    use CcartItemTrait;

    protected $guarded = [];

    protected $hidden = ['product_id', 'customer_id', 'store_id', 'distributes'];
    protected $with = ['product'];
    protected $appends = ['distributes_selected'];
    protected $casts = [
        'is_bonus' => 'boolean',
        'allows_choose_distribute' => 'boolean',
        'has_edit_item_price' => 'boolean'
    ];

    public function customer()
    {
        return $this->belongsto('App\Models\Customer');
    }

    public function store()
    {
        return $this->belongsto('App\Models\Store');
    }

    public function product()
    {
        return $this->belongsto('App\Models\Product');
    }

    public function getDistributesSelectedAttribute()
    {
        return json_decode($this->distributes);
    }
}
