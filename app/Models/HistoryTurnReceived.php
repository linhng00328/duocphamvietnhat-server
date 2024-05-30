<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class HistoryTurnReceived extends Model
{
    use HasFactory;
    use SearchableTrait;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'history_turn_receiveds.title',
            'history_turn_receiveds.description',
        ],
        'joins' => []

    ];
}
