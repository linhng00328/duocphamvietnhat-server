<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class LineItem extends BaseModel
{
    use HasFactory;
    protected $with = ['product'];
    protected $guarded = [];

    protected $hidden = ['store_id', "created_at", "updated_at", "order_id", "product_id", 'distributes'];
    protected $appends = ['distributes_selected', 'reviewed'];

    protected $casts = [
        'has_subtract_inventory' => 'boolean',
        'is_refund' => 'boolean',
        'quantity' => 'integer',
        'is_bonus' => 'boolean'
    ];

    public function order()
    {
        return $this->belongsto('App\Models\Order');
    }

    public function product()
    {

        return $this->belongsto('App\Models\Product');
    }

    public function getDistributesSelectedAttribute()
    {
        return  json_decode($this->distributes);
    }

    public function getReviewedAttribute()
    {
        $request = request();
        $customer = request('customer', $default = null);
        if ($customer  != null) {

            $reviewExists = ProductReviews::where('store_id', $request->store->id)
                ->where('customer_id',  $this->customer_id)
                ->where('product_id', $this->product_id)
                ->where('order_id',  $this->order_id)
                ->first();

            if ($reviewExists != null) return true;
        }

        return false;
    }
}
