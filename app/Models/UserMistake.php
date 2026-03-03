<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMistake extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'mistake_count',
        'correct_at_review_count',
        'mastery_level',
        'last_mistake_at',
        'next_review_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
