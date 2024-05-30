<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ProductCombo extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = ['id', 'store_id', 'product_id', 'created_at', 'updated_at', 'combo_id'];
    protected $appends = ['product'];

    public function getProductAttribute()
    {
     
        $product = Product::where('id', $this->product_id)->get()->first();
        return $product;
    }
}
