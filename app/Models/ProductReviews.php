<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ProductReviews extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'product_id' => 'integer',
    ];

    protected $with = [
        'product'
    ];

    protected $hidden = ['store_id', "customer_id", "updated_at", "product_id", "order_id"];

    protected $appends = ['customer'];

    public function getCustomerAttribute()
    {
        $customer = Customer::where('id', $this->customer_id)->get(['id', 'name', 'phone_number', 'avatar_image'])->first();
        return $customer;
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
