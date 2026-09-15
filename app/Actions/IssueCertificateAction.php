<?php

namespace App\Actions;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\PlatformSetting;
use App\Services\CertificateEligibilityService;
use Illuminate\Support\Facades\DB;

/**
 * The only place a Certificate row gets created. Idempotent per enrollment —
 * calling this twice for the same enrollment returns the existing active
 * certificate rather than issuing a duplicate.
 */
class IssueCertificateAction
{
    public function __construct(private readonly CertificateEligibilityService $eligibility) {}

    public function execute(Enrollment $enrollment): ?Certificate
    {
        $existing = Certificate::query()->where('enrollment_id', $enrollment->id)->where('status', 'active')->first();

        if ($existing) {
            return $existing;
        }

        if (! $this->eligibility->isEligible($enrollment)) {
            return null;
        }

        $passingAttempt = $this->eligibility->passingExamAttempt($enrollment);

        return DB::transaction(function () use ($enrollment, $passingAttempt) {
            $course = $enrollment->course;
            $cohort = $enrollment->cohort;

            return Certificate::query()->create([
                'certificate_id' => $this->generateCertificateId($course),
                'enrollment_id' => $enrollment->id,
                'exam_attempt_id' => $passingAttempt?->id,
                'issued_by' => null, // null = auto-issued by exam pass, not an admin action
                'holder_name' => $enrollment->user->name,
                'course_title' => $course->title,
                'cohort_label' => $cohort?->name,
                'issued_at' => now(),
                'status' => 'active',
            ]);
        });
    }

    private function generateCertificateId($course): string
    {
        $prefix = PlatformSetting::get('certificates', 'id_prefix', 'MKR');
        $courseCode = $course->certificate_code ?: strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $course->title), 0, 2));
        $year = now()->year;

        $pattern = "{$prefix}-{$courseCode}-{$year}-";
        $count = Certificate::query()->where('certificate_id', 'like', "{$pattern}%")->count();
        $sequence = str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);

        return "{$pattern}{$sequence}";
    }
}
