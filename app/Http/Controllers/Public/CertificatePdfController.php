<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificatePdfController extends Controller
{
    /**
     * Public by design — a certificate ID is meant to be shared with an
     * employer, and the PDF contains nothing beyond what the /verify page
     * already shows. Revoked certificates still download (clearly marked),
     * so a check can see exactly what was revoked.
     */
    public function __invoke(string $certificateId)
    {
        $certificate = Certificate::query()->where('certificate_id', $certificateId)->firstOrFail();

        $pdf = Pdf::loadView('certificates.pdf', ['certificate' => $certificate]);

        return $pdf->download("{$certificate->certificate_id}.pdf");
    }
}
