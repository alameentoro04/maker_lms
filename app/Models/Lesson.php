<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    public const TYPES = ['video', 'text', 'mixed', 'quiz', 'assignment', 'resource'];

    protected $fillable = [
        'module_id', 'title', 'type', 'content', 'video_provider', 'video_reference',
        'order', 'is_preview', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_preview' => 'boolean', 'is_published' => 'boolean'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }
}
