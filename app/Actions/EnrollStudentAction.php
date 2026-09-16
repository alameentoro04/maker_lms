<?php

namespace App\Actions;

use App\Exceptions\EnrollmentNotAllowedException;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\EnrollmentStatusHistory;
use App\Models\Order;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Notifications\EnrollmentConfirmed;
use Illuminate\Support\Facades\DB;

/**
 * The single entry point for creating an enrollment. Every enrollment path —
 * Phase 3's admin "manually enroll" action, and Phase 5's payment-webhook
 * activation — MUST go through here rather than creating Enrollment rows
 * directly, so the capacity/deadline/duplicate rules are enforced exactly
 * once, in exactly one place.
 *
 * Rules enforced (per spec Module 4 "Cohort Management" > Rules):
 *  - cohort must be in enrollment_open status
 *  - now() must be within [enrollment_opens_at, enrollment_closes_at]
 *  - now() must be before the cohort start date, unless the
 *    `cohorts.allow_enrollment_after_start` platform setting is true
 *  - cohort must have capacity remaining
 *  - the student must not already hold an active/pending/completed
 *    enrollment in this cohort
 *
 * All rules can be bypassed with $override = true, which only a caller that
 * has already checked `enrollments.manage` permission should ever pass —
 * see Admin\EnrollmentController.
 */
class EnrollStudentAction
{
    public function execute(
        User $student,
        Cohort $cohort,
        ?User $actor = null,
        bool $override = false,
        string $initialStatus = 'active',
        ?Order $order = null,
    ): Enrollment {
        return DB::transaction(function () use ($student, $cohort, $actor, $override, $initialStatus, $order) {
            // Lock the cohort row for the duration of the transaction so two
            // concurrent enrollments can't both slip in under the capacity limit.
            $cohort = Cohort::query()->lockForUpdate()->findOrFail($cohort->id);

            if (! $override) {
                $this->assertCohortIsOpen($cohort);
                $this->assertWithinEnrollmentWindow($cohort);
                $this->assertHasCapacity($cohort);
            }

            $this->assertNoDuplicateEnrollment($student, $cohort);

            $enrollment = Enrollment::query()->create([
                'user_id' => $student->id,
                'course_id' => $cohort->course_id,
                'cohort_id' => $cohort->id,
                'order_id' => $order?->id,
                'status' => $initialStatus,
                'enrolled_at' => now(),
                'access_starts_at' => now(),
                'enrolled_by' => $actor?->id,
                'is_override' => $override,
            ]);

            EnrollmentStatusHistory::query()->create([
                'enrollment_id' => $enrollment->id,
                'from_status' => null,
                'to_status' => $initialStatus,
                'changed_by' => $actor?->id,
                'note' => $override ? 'Enrolled with admin override' : 'Enrolled',
            ]);

            if ($initialStatus === 'active') {
                $student->notify(new EnrollmentConfirmed($enrollment));
            }

            return $enrollment;
        });
    }

    private function assertCohortIsOpen(Cohort $cohort): void
    {
        if ($cohort->status !== 'enrollment_open') {
            throw new EnrollmentNotAllowedException(
                "This cohort is not open for enrollment (status: {$cohort->statusLabel()})."
            );
        }
    }

    private function assertWithinEnrollmentWindow(Cohort $cohort): void
    {
        if ($cohort->enrollment_opens_at && now()->lessThan($cohort->enrollment_opens_at)) {
            throw new EnrollmentNotAllowedException('Enrollment has not opened yet for this cohort.');
        }

        if ($cohort->enrollment_closes_at && now()->greaterThan($cohort->enrollment_closes_at)) {
            throw new EnrollmentNotAllowedException('The enrollment deadline for this cohort has passed.');
        }

        $allowAfterStart = PlatformSetting::get('cohorts', 'allow_enrollment_after_start', false);

        if (! $allowAfterStart && now()->greaterThanOrEqualTo($cohort->start_date)) {
            throw new EnrollmentNotAllowedException('This cohort has already started and enrollment is closed.');
        }
    }

    private function assertHasCapacity(Cohort $cohort): void
    {
        if (! $cohort->hasCapacity()) {
            throw new EnrollmentNotAllowedException('This cohort has reached its maximum capacity.');
        }
    }

    private function assertNoDuplicateEnrollment(User $student, Cohort $cohort): void
    {
        $exists = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('cohort_id', $cohort->id)
            ->whereIn('status', ['pending', 'active', 'completed'])
            ->exists();

        if ($exists) {
            throw new EnrollmentNotAllowedException('This student already has an active enrollment in this cohort.');
        }
    }
}
