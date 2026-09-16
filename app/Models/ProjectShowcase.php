<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ProjectShowcase extends Model
{
    protected $fillable = [
        'course_id', 'submitted_by', 'author_name', 'title', 'description', 'image_path',
        'external_url', 'is_published', 'order',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('order');
    }
}
