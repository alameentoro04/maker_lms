<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gated on courses.manage as a stand-in "content moderation" permission
 * rather than a dedicated moderation.* slug — keeps Staff (payments-only by
 * default) out of moderation without growing the permission set for a
 * single screen. Revisit if moderation duties are ever delegated separately
 * from course management.
 */
class ModerationController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        return Inertia::render('Admin/Moderation/Index', [
            'reports' => CommunityReport::query()
                ->where('status', 'pending')
                ->with(['post.user', 'comment.user', 'reportedBy'])
                ->latest()
                ->get()
                ->map(fn (CommunityReport $r) => [
                    'id' => $r->id,
                    'reason' => $r->reason,
                    'reported_by' => $r->reportedBy->name,
                    'content_type' => $r->post_id ? 'post' : 'comment',
                    'content_body' => $r->post?->body ?? $r->comment?->body,
                    'content_author' => $r->post?->user->name ?? $r->comment?->user->name,
                    'content_hidden' => $r->post?->is_hidden ?? $r->comment?->is_hidden ?? false,
                ]),
        ]);
    }

    public function hide(Request $request, CommunityReport $report): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $report->post?->update(['is_hidden' => true]);
        $report->comment?->update(['is_hidden' => true]);
        $report->update(['status' => 'reviewed']);

        return back()->with('status', 'Content hidden.');
    }

    public function dismiss(Request $request, CommunityReport $report): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $report->update(['status' => 'reviewed']);

        return back()->with('status', 'Report dismissed — content left as-is.');
    }
}
