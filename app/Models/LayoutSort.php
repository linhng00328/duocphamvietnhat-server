<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class LayoutSort extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['store_id', 'json_layouts'];
    protected $appends = ['layouts'];

    public function getLayoutsAttribute()
    {
        return json_decode($this->json_layouts);
    }
}
