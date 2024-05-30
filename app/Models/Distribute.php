<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class Distribute extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['pivot', 'store_id'];

    protected $with = ['element_distributes'];

    public function element_distributes()
    {
        return $this->hasMany('App\Models\ElementDistribute')->orderBy('position', 'ASC');
    }
}
