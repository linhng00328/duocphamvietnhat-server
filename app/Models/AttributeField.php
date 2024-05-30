<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class AttributeField extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['created_at','updated_at','store_id'];
}
