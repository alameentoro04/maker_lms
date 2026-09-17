<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseQuestionReply extends Model
{
    protected $fillable = ['course_question_id', 'user_id', 'reply', 'is_instructor_reply'];

    protected function casts(): array
    {
        return ['is_instructor_reply' => 'boolean'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CourseQuestion::class, 'course_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
