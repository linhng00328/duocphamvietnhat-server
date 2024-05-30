<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ProductImage extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['product_id',"created_at", "updated_at"];
}
