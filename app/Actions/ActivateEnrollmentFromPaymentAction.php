<?php

namespace App\Actions;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The ONE place a verified payment turns into an active enrollment. Called
 * from CheckoutController (after the gateway's own verify() call — never
 * from the frontend redirect alone), the webhook controllers, and
 * Admin\ManualPaymentController (after a human approves the proof).
 *
 * Idempotent by design: webhooks can and do arrive more than once, and a
 * user can hit the callback URL after a webhook already processed the same
 * payment. Re-running this with an already-verified Payment is a no-op that
 * returns the existing enrollment rather than erroring or double-enrolling.
 */
class ActivateEnrollmentFromPaymentAction
{
    public function __construct(private readonly EnrollStudentAction $enroll) {}

    public function activate(Payment $payment, ?string $providerReference = null): ?Enrollment
    {
        return DB::transaction(function () use ($payment, $providerReference) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->isVerified()) {
                return Enrollment::query()->where('order_id', $payment->order_id)->first();
            }

            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'provider_reference' => $providerReference,
            ]);

            $order = $payment->order()->lockForUpdate()->first();
            $order->update(['status' => 'paid']);

            if (! $order->cohort_id) {
                // NOT IMPLEMENTED: course-only (no-cohort) purchases. Every
                // checkout in this build is tied to a cohort, so this
                // shouldn't be reachable — logged rather than silently
                // swallowed in case that assumption ever breaks.
                Log::warning('Paid order has no cohort — cannot activate enrollment', ['order_id' => $order->id]);

                return null;
            }

            return $this->enroll->execute(
                student: $order->user,
                cohort: $order->cohort,
                order: $order,
            );
        });
    }
}
