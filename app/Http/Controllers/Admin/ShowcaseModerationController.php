<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectShowcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseModerationController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        return Inertia::render('Admin/Showcase/Index', [
            'submissions' => ProjectShowcase::query()->whereNotNull('submitted_by')->latest()->get()->map(fn (ProjectShowcase $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'author_name' => $s->author_name,
                'description' => $s->description,
                'is_published' => $s->is_published,
            ]),
        ]);
    }

    public function approve(Request $request, ProjectShowcase $showcase): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $showcase->update(['is_published' => true]);

        return back()->with('status', 'Published to the public showcase.');
    }

    public function reject(Request $request, ProjectShowcase $showcase): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $showcase->update(['is_published' => false]);

        return back()->with('status', 'Kept unpublished.');
    }
}
