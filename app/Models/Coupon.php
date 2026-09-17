<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'issued_to', 'source', 'max_uses', 'used_count', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function isValidFor(User $user): bool
    {
        if ($this->used_count >= $this->max_uses) {
            return false;
        }

        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }

        if ($this->issued_to && $this->issued_to !== $user->id) {
            return false;
        }

        return true;
    }

    /** Discount in minor units for a given order subtotal. */
    public function discountFor(int $subtotal): int
    {
        $discount = $this->type === 'percent' ? (int) round($subtotal * $this->value / 100) : $this->value;

        return min($discount, $subtotal);
    }
}
