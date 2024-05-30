<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class HistoryGiftSpinWheel extends Model
{
    use HasFactory;
    use SearchableTrait;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'history_gift_spin_wheels.text'
        ],
        'joins' => []

    ];
}
