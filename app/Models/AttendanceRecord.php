<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    public const STATUSES = ['pending', 'present', 'absent', 'late', 'excused'];
    public const SOURCES = ['lms_click', 'instructor_verified', 'admin_verified'];

    protected $fillable = ['live_class_id', 'user_id', 'status', 'verification_source', 'joined_at', 'left_at', 'verified_by'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime', 'left_at' => 'datetime'];
    }

    public function liveClass(): BelongsTo
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verification_source !== 'lms_click';
    }
}
