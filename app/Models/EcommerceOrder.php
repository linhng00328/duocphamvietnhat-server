<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    use HasFactory;
    protected $guarded = [];

    // already to ship, cancel, delivered
    public function getLineItemsInTimeAttribute()
    {
        return EcommerceLineItem::where('order_id_in_ecommerce', $this->order_id_in_ecommerce)->get();
    }
}
