<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\LiveClass;
use App\Services\CourseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LiveClassController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function index(Request $request, Course $course): Response
    {
        $user = $request->user();
        abort_unless($this->access->hasAccessToCourse($user, $course), 403);

        $enrollment = $this->access->activeEnrollmentFor($user, $course);
        abort_unless($enrollment && $enrollment->cohort_id, 404);

        $liveClasses = $enrollment->cohort->liveClasses()->orderBy('starts_at')->get();
        $myRecords = AttendanceRecord::query()->where('user_id', $user->id)
            ->whereIn('live_class_id', $liveClasses->pluck('id'))->get()->keyBy('live_class_id');

        return Inertia::render('Student/LiveClasses/Index', [
            'course' => ['title' => $course->title, 'slug' => $course->slug],
            'liveClasses' => $liveClasses->map(fn (LiveClass $lc) => [
                'id' => $lc->id,
                'title' => $lc->title,
                'description' => $lc->description,
                'starts_at' => $lc->starts_at->toDayDateTimeString(),
                'is_past' => $lc->isPast(),
                'status' => $lc->status,
                'meet_url' => $lc->meet_url,
                'recording_url' => $lc->recording_url,
                'my_attendance_status' => $myRecords[$lc->id]->status ?? null,
                'clicked_join' => isset($myRecords[$lc->id]),
            ]),
        ]);
    }

    /** Records a "joined" click — NOT verified attendance on its own. See spec principle in AttendanceRecord migration. */
    public function join(Request $request, LiveClass $liveClass): RedirectResponse
    {
        $user = $request->user();
        $enrollment = $this->access->activeEnrollmentFor($user, $liveClass->cohort->course);
        abort_unless($enrollment && $enrollment->cohort_id === $liveClass->cohort_id, 403);

        AttendanceRecord::query()->updateOrCreate(
            ['live_class_id' => $liveClass->id, 'user_id' => $user->id],
            ['joined_at' => now(), 'verification_source' => 'lms_click']
        );

        return back();
    }
}
