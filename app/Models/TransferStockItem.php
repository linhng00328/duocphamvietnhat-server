<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class TransferStockItem extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $with = ['product'];
 
    public function product()
    {

        return $this->belongsto('App\Models\Product');
    }
}
