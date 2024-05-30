<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class PayCollaborator extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $with = [
        'collaborator',
    ];

    public function collaborator()
    {
        return $this->belongsto('App\Models\Collaborator');
    }
}
