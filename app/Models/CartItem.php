<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'cohort_id', 'package_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function amount(): int
    {
        return $this->cohort?->course->price ?? $this->package?->price ?? 0;
    }

    public function currency(): string
    {
        return $this->cohort?->course->currency ?? $this->package?->currency ?? 'NGN';
    }

    public function label(): string
    {
        return $this->cohort
            ? "{$this->cohort->course->title} — {$this->cohort->name}"
            : ($this->package?->title ?? 'Item');
    }
}
