<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;

class LastSubmitQuiz extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'history_submit_quizzes'
    ];
    protected $hidden = ['history_submit_quizzes_json'];

    public function getHistorySubmitQuizzesAttribute()
    {
        return json_decode($this->history_submit_quizzes_json);
    }

    public function train_quiz()
    {
        return $this->belongsTo(TrainQuiz::class, 'quiz_id');
    }
}
