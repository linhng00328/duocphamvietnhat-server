<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryGiftGuessNumber extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    protected $appends = [
        // 'guess_number',
        'guess_number_result'
    ];

    public function getGuessNumberAttribute()
    {
        $guessNumberExist = GuessNumber::where([
            ['store_id', $this->store_id],
            ['id', $this->guess_number_id]
        ])->first();

        return $guessNumberExist;
    }

    public function getGuessNumberResultAttribute()
    {
        $guessNumberResultExist = GuessNumberResult::where([
            ['store_id', $this->store_id],
            ['guess_number_id', $this->guess_number_id],
            ['id', $this->guess_number_result_id]
        ])->first();

        return $guessNumberResultExist;
    }
}
