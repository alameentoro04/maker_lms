<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id', 'instructions', 'due_at', 'allowed_file_types', 'max_file_size_kb',
        'max_submissions', 'allow_resubmission', 'passing_score',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'allowed_file_types' => 'array',
            'allow_resubmission' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPastDue(): bool
    {
        return $this->due_at !== null && now()->greaterThan($this->due_at);
    }
}
