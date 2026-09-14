<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Cohort extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'draft', 'upcoming', 'enrollment_open', 'enrollment_closed',
        'in_progress', 'completed', 'archived', 'cancelled',
    ];

    protected $fillable = [
        'course_id', 'name', 'slug', 'start_date', 'end_date', 'enrollment_opens_at',
        'enrollment_closes_at', 'capacity', 'live_platform', 'learning_model', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'enrollment_opens_at' => 'datetime',
            'enrollment_closes_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_instructor');
    }

    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollmentCount(): int
    {
        return $this->enrollments()->whereIn('status', ['pending', 'active', 'completed'])->count();
    }

    public function hasCapacity(): bool
    {
        return $this->activeEnrollmentCount() < $this->capacity;
    }

    /**
     * Public-facing enrollment availability. This checks dates/status/capacity
     * ONLY — it does not know about real enrollment counts yet (Phase 5), so
     * "capacity reached" isn't enforced here until enrollments exist. Never
     * infer "seats left" from this until that data is real.
     */
    public function isAcceptingEnrollment(): bool
    {
        if ($this->status !== 'enrollment_open') {
            return false;
        }

        if ($this->enrollment_closes_at && now()->greaterThan($this->enrollment_closes_at)) {
            return false;
        }

        if ($this->enrollment_opens_at && now()->lessThan($this->enrollment_opens_at)) {
            return false;
        }

        return true;
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
