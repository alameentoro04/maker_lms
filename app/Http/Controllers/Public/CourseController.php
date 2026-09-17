<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseBookmark;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Course::query()->published()->with('category');

        if ($search = $request->query('q')) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%"));
        }

        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($level = $request->query('level')) {
            $query->where('level', $level);
        }

        $courses = $query->get()->map(fn (Course $course) => [
            'title' => $course->title,
            'slug' => $course->slug,
            'summary' => $course->summary,
            'level' => $course->level,
            'duration_weeks' => $course->duration_weeks,
            'price' => $course->formattedPrice(),
            'category' => $course->category?->name,
            'average_rating' => $course->averageRating(),
            'review_count' => $course->reviewCount(),
        ]);

        return Inertia::render('Public/Courses/Index', [
            'courses' => $courses,
            'categories' => Category::query()->orderBy('order')->get(['name', 'slug']),
            'filters' => ['q' => $search, 'category' => $categorySlug, 'level' => $level],
        ]);
    }

    public function show(Request $request, Course $course): Response
    {
        abort_unless($course->status === 'published', 404);

        $course->load([
            'instructors.profile',
            'cohorts' => fn ($q) => $q->orderBy('start_date'),
            'reviews' => fn ($q) => $q->published()->with('user')->latest(),
            'questions' => fn ($q) => $q->where('is_hidden', false)->with(['user', 'replies.user'])->latest(),
        ]);

        $user = $request->user();
        $isBookmarked = $user && CourseBookmark::query()->where('user_id', $user->id)->where('course_id', $course->id)->exists();

        return Inertia::render('Public/Courses/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'summary' => $course->summary,
                'description' => $course->description,
                'objectives' => $course->objectives ?? [],
                'requirements' => $course->requirements ?? [],
                'level' => $course->level,
                'duration_weeks' => $course->duration_weeks,
                'price' => $course->formattedPrice(),
                'average_rating' => $course->averageRating(),
                'review_count' => $course->reviewCount(),
            ],
            'instructors' => $course->instructors->map(fn ($i) => [
                'name' => $i->name,
                'headline' => $i->profile?->headline,
                'id' => $i->id,
            ]),
            'cohorts' => $course->cohorts->map(fn ($c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'start_date' => $c->start_date->toFormattedDateString(),
                'end_date' => $c->end_date->toFormattedDateString(),
                'status' => $c->statusLabel(),
                'accepting_enrollment' => $c->isAcceptingEnrollment(),
                'capacity' => $c->capacity,
            ]),
            'reviews' => $course->reviews->map(fn ($r) => [
                'id' => $r->id,
                'author' => $r->user->name,
                'rating' => $r->rating,
                'review' => $r->review,
                'created_at' => $r->created_at->toFormattedDateString(),
            ]),
            'questions' => $course->questions->map(fn ($q) => [
                'id' => $q->id,
                'author' => $q->user->name,
                'question' => $q->question,
                'created_at' => $q->created_at->diffForHumans(),
                'replies' => $q->replies->map(fn ($r) => [
                    'author' => $r->user->name,
                    'reply' => $r->reply,
                    'is_instructor_reply' => $r->is_instructor_reply,
                    'created_at' => $r->created_at->diffForHumans(),
                ]),
            ]),
            'is_bookmarked' => $isBookmarked,
        ]);
    }
}
