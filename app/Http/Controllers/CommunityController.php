<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommunityPostRequest;
use App\Models\Cohort;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\CommunityReport;
use App\Services\CourseAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Shared across every authenticated role — students post in their cohort's
 * discussion, instructors/admins can see and reply too. Heavy moderation
 * (hiding content, resolving reports) is admin-only — see Admin\ModerationController.
 */
class CommunityController extends Controller
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function index(Request $request, ?Cohort $cohort = null): Response
    {
        $user = $request->user();

        if ($cohort) {
            $hasAccess = $user->hasPermission('courses.manage')
                || $cohort->instructors()->where('users.id', $user->id)->exists()
                || $this->access->activeEnrollmentFor($user, $cohort->course) !== null;

            abort_unless($hasAccess, 403);
        }

        $posts = CommunityPost::query()
            ->visible()
            ->where('cohort_id', $cohort?->id)
            ->with(['user', 'comments' => fn ($q) => $q->where('is_hidden', false)->with('user')])
            ->latest()
            ->get();

        return Inertia::render('Community/Index', [
            'cohort' => $cohort ? ['id' => $cohort->id, 'name' => $cohort->name, 'slug' => $cohort->slug] : null,
            'posts' => $posts->map(fn (CommunityPost $p) => [
                'id' => $p->id,
                'author' => $p->user->name,
                'author_id' => $p->user->id,
                'body' => $p->body,
                'created_at' => $p->created_at->diffForHumans(),
                'comments' => $p->comments->map(fn (CommunityComment $c) => [
                    'id' => $c->id,
                    'author' => $c->user->name,
                    'body' => $c->body,
                    'created_at' => $c->created_at->diffForHumans(),
                ]),
            ]),
        ]);
    }

    public function store(CommunityPostRequest $request, ?Cohort $cohort = null): RedirectResponse
    {
        CommunityPost::query()->create([
            'cohort_id' => $cohort?->id,
            'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        return back()->with('status', 'Posted.');
    }

    public function storeComment(CommunityPostRequest $request, CommunityPost $post): RedirectResponse
    {
        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated()['body'],
        ]);

        return back()->with('status', 'Comment added.');
    }

    public function report(Request $request, CommunityPost $post): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        CommunityReport::query()->create([
            'community_post_id' => $post->id,
            'reported_by' => $request->user()->id,
            'reason' => $validated['reason'],
        ]);

        return back()->with('status', 'Reported — a moderator will review it.');
    }
}
