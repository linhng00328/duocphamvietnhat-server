<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
class HistoryPayOrder extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['id', 'store_id', 'order_id'];
}
