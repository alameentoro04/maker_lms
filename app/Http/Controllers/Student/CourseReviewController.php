<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\CourseReviewRequest;
use App\Models\Course;
use App\Models\CourseReview;
use App\Services\CourseAccessService;
use Illuminate\Http\RedirectResponse;

class CourseReviewController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function store(CourseReviewRequest $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        $enrollment = $this->access->activeEnrollmentFor($user, $course);

        // Only students who actually hold (or held) an active enrollment can
        // review — never gate on "currently enrolled" alone, since a
        // completed student should still be able to review.
        abort_unless($enrollment, 403, 'Only enrolled students can review this course.');

        CourseReview::query()->updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $user->id],
            [...$request->validated(), 'enrollment_id' => $enrollment->id]
        );

        return back()->with('status', 'Thanks for your review!');
    }
}
