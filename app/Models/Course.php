<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'title', 'slug', 'summary', 'description', 'objectives',
        'requirements', 'level', 'duration_weeks', 'status', 'price', 'currency',
        'thumbnail_path',
    ];

    protected function casts(): array
    {
        return [
            'objectives' => 'array',
            'requirements' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructor');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isFree(): bool
    {
        return $this->price === 0;
    }

    /** Price formatted for display — minor units to major, e.g. 15000000 kobo -> ₦150,000 */
    public function formattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        $symbol = match ($this->currency) {
            'NGN' => '₦',
            'USD' => '$',
            default => $this->currency.' ',
        };

        return $symbol.number_format($this->price / 100);
    }
}
