<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Package extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'price', 'currency', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function cohorts(): BelongsToMany
    {
        return $this->belongsToMany(Cohort::class, 'package_cohort');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function formattedPrice(): string
    {
        $symbol = match ($this->currency) { 'NGN' => '₦', 'USD' => '$', default => $this->currency.' ' };

        return $symbol.number_format($this->price / 100);
    }
}
