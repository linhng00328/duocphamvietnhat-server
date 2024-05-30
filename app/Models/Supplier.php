<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;
use AjCastro\Searchable\Searchable;
class Supplier extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'name',
        ],
    ];

}
