<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Schema only — no refund workflow/UI yet. See README "Known gaps". */
class Refund extends Model
{
    protected $fillable = ['payment_id', 'amount', 'reason', 'status', 'processed_by', 'processed_at'];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
