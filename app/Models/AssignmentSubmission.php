<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    public const STATUSES = ['submitted', 'graded', 'returned'];

    protected $fillable = [
        'assignment_id', 'user_id', 'enrollment_id', 'attempt_number', 'text_response',
        'external_link', 'file_path', 'file_original_name', 'status', 'grade',
        'instructor_feedback', 'graded_by', 'graded_at',
    ];

    protected function casts(): array
    {
        return ['graded_at' => 'datetime'];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }
}
