<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    public const TYPES = ['multiple_choice', 'true_false'];

    protected $fillable = ['exam_id', 'type', 'question', 'options', 'explanation', 'order', 'points'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function correctOptionIndex(): ?int
    {
        foreach ($this->options as $index => $option) {
            if (! empty($option['is_correct'])) {
                return $index;
            }
        }

        return null;
    }

    /** Options with is_correct stripped — what the student's browser should ever see before submitting. */
    public function optionsForStudent(): array
    {
        return array_map(fn ($o) => ['text' => $o['text']], $this->options);
    }
}
