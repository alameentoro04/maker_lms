<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\LessonProgress;
use App\Models\PlatformSetting;

/**
 * Default eligibility rule per spec: final exam score >= passing threshold.
 * Optional additional requirement (full lesson completion) is configurable
 * via platform_settings and OFF by default — see README for why attendance
 * and assignment-score requirements listed in the spec are NOT enforced here
 * yet (attendance doesn't exist until Phase 7; assignment-score gating would
 * need a defined aggregation rule the spec doesn't specify).
 */
class CertificateEligibilityService
{
    public function passingExamAttempt(Enrollment $enrollment): ?ExamAttempt
    {
        $exam = $enrollment->cohort?->exam;

        if (! $exam) {
            return null;
        }

        return ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $enrollment->user_id)
            ->where('passed', true)
            ->latest('submitted_at')
            ->first();
    }

    public function isEligible(Enrollment $enrollment): bool
    {
        if (! $this->passingExamAttempt($enrollment)) {
            return false;
        }

        if (PlatformSetting::get('certificates', 'require_full_lesson_completion', false)) {
            $lessons = $enrollment->course->modules->flatMap->lessons->where('is_published', true);
            $completed = LessonProgress::query()
                ->where('user_id', $enrollment->user_id)
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->whereNotNull('completed_at')
                ->count();

            if ($completed < $lessons->count()) {
                return false;
            }
        }

        return true;
    }
}
