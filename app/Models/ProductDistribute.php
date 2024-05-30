<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ProductDistribute extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['product_detail_id',"created_at", "updated_at"];

    public function detail()
    {
        return $this->belongsto('App\Models\ProductDetail');
    }
}
