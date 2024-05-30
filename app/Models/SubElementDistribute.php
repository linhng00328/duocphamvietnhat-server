<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class SubElementDistribute extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = ['store_id', 'pivot'];
    public function distribute()
    {
        return $this->belongsto('App\Models\Distribute');
    }

    public function element_distribute()
    {
        return $this->belongsto('App\Models\ElementDistribute');
    }
}
