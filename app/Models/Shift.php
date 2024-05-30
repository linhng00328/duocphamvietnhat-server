<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;
use AjCastro\Searchable\Searchable;

class Shift extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];
    protected $searchable = [
        'columns' => [
            'name',
        ],
    ];
    protected $appends = ['days_of_week_list'];

    public function getDaysOfWeekListAttribute()
    {
        return json_decode($this->days_of_week);
    }
}
