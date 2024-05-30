<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
class ImportStockItem extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $with = ['product'];
 
    public function product()
    {

        return $this->belongsto('App\Models\Product');
    }
}
