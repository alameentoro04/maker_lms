<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    /**
     * Seeds the configurable defaults named explicitly in the spec so nothing
     * ends up hard-coded in business logic.
     */
    public function run(): void
    {
        $defaults = [
            ['group' => 'exams', 'key' => 'default_passing_score', 'value' => '70', 'type' => 'integer'],
            ['group' => 'cohorts', 'key' => 'default_duration_weeks', 'value' => '4', 'type' => 'integer'],
            ['group' => 'cohorts', 'key' => 'default_max_students', 'value' => '30', 'type' => 'integer'],
            ['group' => 'cohorts', 'key' => 'allow_enrollment_after_start', 'value' => 'false', 'type' => 'boolean'],
            ['group' => 'cohorts', 'key' => 'default_live_platform', 'value' => 'google_meet', 'type' => 'string'],
            ['group' => 'certificates', 'key' => 'require_min_attendance_percent', 'value' => '', 'type' => 'integer'],
            ['group' => 'certificates', 'key' => 'require_full_lesson_completion', 'value' => 'false', 'type' => 'boolean'],
            ['group' => 'certificates', 'key' => 'id_prefix', 'value' => 'MKR', 'type' => 'string'],
            ['group' => 'general', 'key' => 'support_email', 'value' => 'support@makers.al-ismail.com.ng', 'type' => 'string'],
            // Placeholder — replace with the real account before enabling bank transfer in production.
            ['group' => 'payments', 'key' => 'bank_name', 'value' => 'Not yet configured', 'type' => 'string'],
            ['group' => 'payments', 'key' => 'account_name', 'value' => 'Not yet configured', 'type' => 'string'],
            ['group' => 'payments', 'key' => 'account_number', 'value' => 'Not yet configured', 'type' => 'string'],
            ['group' => 'referrals', 'key' => 'discount_percent', 'value' => '10', 'type' => 'integer'],
        ];

        foreach ($defaults as $setting) {
            PlatformSetting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}
