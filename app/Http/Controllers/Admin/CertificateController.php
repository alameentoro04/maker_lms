<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('certificates.view') || $request->user()->hasPermission('certificates.manage'), 403);

        return Inertia::render('Admin/Certificates/Index', [
            'certificates' => Certificate::query()->latest('issued_at')->get()->map(fn (Certificate $c) => [
                'id' => $c->id,
                'certificate_id' => $c->certificate_id,
                'holder_name' => $c->holder_name,
                'course_title' => $c->course_title,
                'cohort_label' => $c->cohort_label,
                'issued_at' => $c->issued_at->toFormattedDateString(),
                'status' => $c->status,
                'auto_issued' => $c->issued_by === null,
            ]),
        ]);
    }

    public function revoke(Request $request, Certificate $certificate): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('certificates.manage'), 403);

        $validated = $request->validate(['revoked_reason' => ['required', 'string', 'max:500']]);

        $certificate->update([
            'status' => 'revoked',
            'revoked_reason' => $validated['revoked_reason'],
            'revoked_at' => now(),
        ]);

        return back()->with('status', 'Certificate revoked.');
    }
}
