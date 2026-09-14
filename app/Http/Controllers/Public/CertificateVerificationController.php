<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

class CertificateVerificationController extends Controller
{
    public function lookup(): Response
    {
        return Inertia::render('Public/VerifyLookup');
    }

    /**
     * Public lookup. Deliberately returns only the fields a verifier needs
     * (holder name, course, status) — never enrollment, contact, or payment
     * details, even though this controller has no auth guard at all.
     */
    public function show(string $certificateId): Response
    {
        $certificate = Certificate::query()->where('certificate_id', $certificateId)->first();

        return Inertia::render('Public/Verify', [
            'certificateId' => $certificateId,
            'result' => $certificate ? [
                'found' => true,
                'holder_name' => $certificate->holder_name,
                'course_title' => $certificate->course_title,
                'cohort_label' => $certificate->cohort_label,
                'issued_at' => $certificate->issued_at->toFormattedDateString(),
                'status' => $certificate->status,
                'valid' => $certificate->isValid(),
            ] : ['found' => false],
        ]);
    }
}
