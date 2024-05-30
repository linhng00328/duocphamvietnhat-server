<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ElementDistribute extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['distribute_id', 'store_id', 'pivot'];
    protected $with = ['sub_element_distributes'];

    public function distribute()
    {
        return $this->belongsto('App\Models\Distribute');
    }

    public function sub_element_distributes()
    {
        return $this->hasMany('App\Models\SubElementDistribute')->orderBy('position', 'ASC');
    }
}
