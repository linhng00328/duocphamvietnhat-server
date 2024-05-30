<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
class ImportTimeHistory extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['id', 'store_id', 'product_id', 'created_at', 'updated_at', 'import_stock_id','branch_id'];
}
 