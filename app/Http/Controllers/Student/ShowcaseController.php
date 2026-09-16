<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitShowcaseRequest;
use App\Models\ProjectShowcase;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseController extends Controller
{
    public function index(): Response
    {
        $mine = ProjectShowcase::query()
            ->where('submitted_by', request()->user()->id)
            ->latest()
            ->get()
            ->map(fn (ProjectShowcase $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'is_published' => $s->is_published,
            ]);

        return Inertia::render('Student/Showcase/Index', ['mine' => $mine]);
    }

    public function store(SubmitShowcaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('showcase', 'public')
            : null;

        ProjectShowcase::query()->create([
            'submitted_by' => $request->user()->id,
            'author_name' => $request->user()->name,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
            'image_path' => $imagePath,
            'is_published' => false, // pending admin review — see Admin\ShowcaseModerationController
        ]);

        return back()->with('status', 'Submitted — an admin will review it before it appears publicly.');
    }
}
