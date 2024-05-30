<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainQuiz extends Model
{
    use HasFactory, Searchable;
    protected $guarded = [];
    protected $casts = [
        'auto_change_order_questions' => 'boolean',
        'auto_change_order_answer' => 'boolean',
        'show' => 'boolean',
        'is_completed' => 'boolean',
    ];
    protected $searchable = [
        'columns' => [
            'title',
        ],
    ];

    public function last_submit_quizzes()
    {
        return $this->hasMany(LastSubmitQuiz::class, 'quiz_id');
    }

    public function train_course()
    {
        return $this->belongsTo(TrainCourse::class);
    }
}
