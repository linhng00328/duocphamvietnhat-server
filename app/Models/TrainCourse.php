<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class TrainCourse extends BaseModel
{
    use HasFactory, Searchable;
    protected $guarded = [];
    protected $searchable = [
        'columns' => [
            'title',
        ],
    ];
}
