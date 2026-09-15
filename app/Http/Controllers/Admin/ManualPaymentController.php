<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ActivateEnrollmentFromPaymentAction;
use App\Exceptions\EnrollmentNotAllowedException;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\ManualPaymentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualPaymentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Enrollment::class); // enrollments.manage/view gate

        return Inertia::render('Admin/ManualPayments/Index', [
            'submissions' => ManualPaymentSubmission::query()
                ->with(['order.user', 'order.course', 'order.cohort', 'payment'])
                ->latest()
                ->get()
                ->map(fn (ManualPaymentSubmission $s) => [
                    'id' => $s->id,
                    'student' => $s->order->user->name,
                    'course' => $s->order->course->title,
                    'cohort' => $s->order->cohort?->name,
                    'amount' => $s->order->formattedAmount(),
                    'provider' => $s->payment->provider,
                    'has_proof' => (bool) $s->proof_path,
                    'note' => $s->note,
                    'status' => $s->status,
                    'submitted_at' => $s->created_at->toDayDateTimeString(),
                ]),
        ]);
    }

    public function downloadProof(ManualPaymentSubmission $submission): StreamedResponse
    {
        $this->authorize('viewAny', Enrollment::class);
        abort_unless($submission->proof_path, 404);

        return Storage::disk('local')->download($submission->proof_path);
    }

    public function approve(Request $request, ManualPaymentSubmission $submission): RedirectResponse
    {
        $this->authorize('create', Enrollment::class); // enrollments.manage gate

        abort_if($submission->status !== 'pending', 422, 'This submission has already been reviewed.');

        $submission->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        try {
            (new ActivateEnrollmentFromPaymentAction())->activate($submission->payment);
        } catch (EnrollmentNotAllowedException $e) {
            return back()->with('status', 'Approved, but enrollment failed: '.$e->getMessage());
        }

        return back()->with('status', 'Payment approved — student enrolled.');
    }

    public function reject(Request $request, ManualPaymentSubmission $submission): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $validated = $request->validate(['review_note' => ['nullable', 'string', 'max:1000']]);

        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        $submission->payment->update(['status' => 'failed']);

        return back()->with('status', 'Submission rejected.');
    }
}
