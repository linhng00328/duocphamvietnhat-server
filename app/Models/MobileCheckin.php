<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
class MobileCheckin extends Model
{
    use HasFactory;
    use SearchableTrait;
    
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'staff.name' => 1,
        ],
        'joins' => [
            'staff' => ['staff.id', 'date_timekeeping_histories.staff_id']
        ]
    ];
 
}
