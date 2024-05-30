<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class HistorySms extends BaseModel
{
    use HasFactory;
    use SearchableTrait;

    protected $guarded = [];

    const TYPE_AUTH = 'TYPE_AUTH';
    const TYPE_ORDER = 'TYPE_ORDER';

    protected $searchable = [
        'columns' => [
            'phone' => 10,
            'content' => 6,
            'partner' => 5,
        ]
    ];
}
