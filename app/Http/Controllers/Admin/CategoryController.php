<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Course::class);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::query()->withCount('courses')->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        Category::query()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'order' => $validated['order'] ?? 0,
        ]);

        return back()->with('status', 'Category added.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', Course::class);

        abort_if($category->courses()->exists(), 422, 'Cannot delete a category that still has courses.');

        $category->delete();

        return back()->with('status', 'Category removed.');
    }
}
