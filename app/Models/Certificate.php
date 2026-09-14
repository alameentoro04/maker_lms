<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_id', 'holder_name', 'course_title', 'cohort_label',
        'issued_at', 'status', 'revoked_reason', 'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return $this->status === 'active';
    }
}
