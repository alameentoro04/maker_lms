<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseBookmark;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseBookmarkController extends Controller
{
    public function toggle(Request $request, Course $course): RedirectResponse
    {
        $existing = CourseBookmark::query()->where('user_id', $request->user()->id)->where('course_id', $course->id)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Removed from bookmarks.');
        }

        CourseBookmark::query()->create(['user_id' => $request->user()->id, 'course_id' => $course->id]);

        return back()->with('status', 'Bookmarked.');
    }

    public function index(Request $request): Response
    {
        $bookmarks = CourseBookmark::query()->where('user_id', $request->user()->id)->with('course.category')->latest()->get();

        return Inertia::render('Student/Bookmarks/Index', [
            'bookmarks' => $bookmarks->map(fn (CourseBookmark $b) => [
                'course_title' => $b->course->title,
                'course_slug' => $b->course->slug,
                'summary' => $b->course->summary,
            ]),
        ]);
    }
}
