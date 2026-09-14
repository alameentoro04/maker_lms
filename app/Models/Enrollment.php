<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['pending', 'active', 'completed', 'cancelled', 'suspended', 'expired'];

    protected $fillable = [
        'user_id', 'course_id', 'cohort_id', 'status', 'enrolled_at', 'access_starts_at',
        'access_ends_at', 'completed_at', 'enrolled_by', 'is_override',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'access_starts_at' => 'datetime',
            'access_ends_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_override' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(EnrollmentStatusHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * The single place that decides whether a student can open a given
     * course's content right now. Every lesson/exam/live-class access check
     * in later phases should route through here, never through payment
     * status directly.
     */
    public function grantsAccess(): bool
    {
        if (! in_array($this->status, ['active', 'completed'], true)) {
            return false;
        }

        if ($this->access_ends_at && now()->greaterThan($this->access_ends_at)) {
            return false;
        }

        return true;
    }
}
