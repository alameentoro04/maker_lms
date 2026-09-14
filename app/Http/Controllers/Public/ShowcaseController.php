<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProjectShowcase;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Showcase', [
            'items' => ProjectShowcase::query()->published()->with('course:id,title')->get()->map(fn ($item) => [
                'title' => $item->title,
                'author_name' => $item->author_name,
                'description' => $item->description,
                'image_path' => $item->image_path,
                'external_url' => $item->external_url,
                'course' => $item->course?->title,
            ]),
        ]);
    }
}
