<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseQuestionReplyRequest;
use App\Http\Requests\CourseQuestionRequest;
use App\Models\Course;
use App\Models\CourseQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Open to any authenticated user — asking (and answering) questions on a public course page is not enrollment-gated, unlike cohort Community. */
class CourseQuestionController extends Controller
{
    public function store(CourseQuestionRequest $request, Course $course): RedirectResponse
    {
        $course->questions()->create([
            'user_id' => $request->user()->id,
            'question' => $request->validated()['question'],
        ]);

        return back()->with('status', 'Question posted.');
    }

    public function reply(CourseQuestionReplyRequest $request, CourseQuestion $question): RedirectResponse
    {
        $user = $request->user();
        $course = $question->course;
        $isInstructor = $course->instructors()->where('users.id', $user->id)->exists() || $user->hasPermission('courses.manage');

        $question->replies()->create([
            'user_id' => $user->id,
            'reply' => $request->validated()['reply'],
            'is_instructor_reply' => $isInstructor,
        ]);

        return back()->with('status', 'Reply posted.');
    }
}
