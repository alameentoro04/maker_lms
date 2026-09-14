<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Database\Seeder;

class CertificateDemoSeeder extends Seeder
{
    /**
     * A single demo record so /verify/{id} is testable before Phase 6 wires
     * up real issuance. Clearly a demo ID — not a real certificate.
     */
    public function run(): void
    {
        Certificate::query()->updateOrCreate(
            ['certificate_id' => 'MKR-GD-2026-000000'],
            [
                'holder_name' => 'Demo Certificate (seed data)',
                'course_title' => 'Graphic Design',
                'cohort_label' => 'Graphic Design — Cohort 1',
                'issued_at' => now(),
                'status' => 'active',
            ]
        );
    }
}
