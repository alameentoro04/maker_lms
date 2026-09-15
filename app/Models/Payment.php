<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const PROVIDERS = ['paystack', 'flutterwave', 'bank_transfer', 'manual'];
    public const STATUSES = ['pending', 'verified', 'failed'];

    protected $fillable = [
        'order_id', 'provider', 'reference', 'provider_reference', 'status',
        'amount', 'currency', 'meta', 'verified_at',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array', 'verified_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function manualSubmission(): HasOne
    {
        return $this->hasOne(ManualPaymentSubmission::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isManual(): bool
    {
        return in_array($this->provider, ['bank_transfer', 'manual'], true);
    }
}
