<?php

namespace App\Actions;

use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Referral;
use App\Services\CouponService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The ONE place a verified payment turns into enrollment(s). Called from
 * CheckoutController (after the gateway's own verify() call — never from the
 * frontend redirect alone), the webhook controllers, and
 * Admin\ManualPaymentController (after a human approves the proof).
 *
 * An order can carry several order_items now (cart checkout, or a package
 * bundling multiple cohorts) — this loops every item and enrolls into every
 * cohort it resolves to, via EnrollStudentAction every time, so the
 * capacity/deadline/duplicate rules are never bypassed for a cart purchase.
 *
 * Idempotent by design: webhooks can and do arrive more than once, and a
 * user can hit the callback URL after a webhook already processed the same
 * payment. Re-running this with an already-verified Payment is a no-op that
 * returns the existing enrollments rather than erroring or double-enrolling.
 */
class ActivateEnrollmentFromPaymentAction
{
    public function __construct(private readonly EnrollStudentAction $enroll) {}

    /** @return Collection<int, Enrollment> */
    public function activate(Payment $payment, ?string $providerReference = null): Collection
    {
        return DB::transaction(function () use ($payment, $providerReference) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->isVerified()) {
                return Enrollment::query()->where('order_id', $payment->order_id)->get();
            }

            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'provider_reference' => $providerReference,
            ]);

            $order = $payment->order()->lockForUpdate()->first();
            $order->update(['status' => 'paid']);

            if ($order->coupon_id) {
                (new CouponService())->markUsed($order->coupon);
            }

            $cohortIds = $order->items->flatMap(function ($item) {
                if ($item->cohort_id) {
                    return [$item->cohort_id];
                }
                if ($item->package_id) {
                    return $item->package->cohorts->pluck('id');
                }

                return [];
            })->unique();

            if ($cohortIds->isEmpty()) {
                // NOT IMPLEMENTED: course-only (no-cohort) purchases. Every
                // checkout item in this build resolves to a cohort (directly
                // or via a package), so this shouldn't be reachable — logged
                // rather than silently swallowed in case that ever breaks.
                Log::warning('Paid order has no resolvable cohort — cannot activate enrollment', ['order_id' => $order->id]);

                return collect();
            }

            $enrollments = $cohortIds->map(function ($cohortId) use ($order) {
                $cohort = Cohort::query()->findOrFail($cohortId);

                return $this->enroll->execute(student: $order->user, cohort: $cohort, order: $order);
            });

            $this->rewardReferrerIfApplicable($order);

            return $enrollments;
        });
    }

    private function rewardReferrerIfApplicable(Order $order): void
    {
        $referral = Referral::query()->where('referred_id', $order->user_id)->where('status', 'pending')->first();

        if (! $referral) {
            return;
        }

        $referral->update(['status' => 'rewarded', 'order_id' => $order->id]);
        (new CouponService())->issueReferralCoupon($referral->referrer, 'referral_referrer');
    }
}
