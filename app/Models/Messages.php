<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Messages extends BaseModel
{
    use HasFactory;

    protected $table = "messages";
    protected $guarded = [];
    protected $hidden = ['store_id', 'product_id', 'device_id'];


    protected $appends = ['product'];
    protected $casts = [
        'is_user' => 'boolean',
    ];

    public function getProductAttribute()
    {

        $product = Product::where('id', $this->product_id)->where('store_id', $this->store_id)->get()->first();
        return $product;
    }
}
