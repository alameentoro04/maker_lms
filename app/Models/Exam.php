<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = ['cohort_id', 'title', 'instructions', 'passing_score', 'time_limit_minutes', 'attempt_limit', 'randomize_questions'];

    protected function casts(): array
    {
        return ['randomize_questions' => 'boolean'];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function effectivePassingScore(): int
    {
        return $this->passing_score ?? PlatformSetting::get('exams', 'default_passing_score', 70);
    }
}
