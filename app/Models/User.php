<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'name', 'email', 'phone', 'password', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function instructorCourses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_instructor');
    }

    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Central permission check. Super Admin always passes — see AuthServiceProvider::boot()
     * for the Gate::before() bypass; this method is what every Policy/Blade/Inertia check calls.
     */
    public function hasPermission(string $slug): bool
    {
        return $this->role?->permissions->contains('slug', $slug) ?? false;
    }

    public function hasRole(string ...$slugs): bool
    {
        return in_array($this->role?->slug, $slugs, true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Where an authenticated user lands after login — drives the single
     * /dashboard redirect route rather than hard-coding role checks everywhere.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role?->slug) {
            'super_admin', 'admin', 'staff' => 'admin.dashboard',
            'instructor' => 'instructor.dashboard',
            default => 'student.dashboard',
        };
    }
}
