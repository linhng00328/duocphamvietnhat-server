<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class SearchHistory extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = ['customer_id','store_id'];
}
