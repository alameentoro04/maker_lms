<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LiveClassRequest;
use App\Models\AttendanceRecord;
use App\Models\Cohort;
use App\Models\LiveClass;
use App\Models\Role;
use App\Notifications\LiveClassScheduled;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LiveClassController extends Controller
{
    public function index(Request $request, Cohort $cohort): Response
    {
        $this->authorizeCohortAccess($request, $cohort);

        return Inertia::render('Admin/LiveClasses/Index', [
            'cohort' => ['id' => $cohort->id, 'name' => $cohort->name],
            'liveClasses' => $cohort->liveClasses()->orderByDesc('starts_at')->get()->map(fn (LiveClass $lc) => [
                'id' => $lc->id,
                'title' => $lc->title,
                'starts_at' => $lc->starts_at->toDayDateTimeString(),
                'status' => $lc->status,
            ]),
        ]);
    }

    public function store(LiveClassRequest $request, Cohort $cohort): RedirectResponse
    {
        $this->authorizeCohortAccess($request, $cohort);

        $liveClass = $cohort->liveClasses()->create([
            ...$request->validated(),
            'instructor_id' => $request->user()->hasRole(Role::INSTRUCTOR) ? $request->user()->id : null,
        ]);

        $students = $cohort->enrollments()->whereIn('status', ['active', 'completed'])->with('user')->get()->pluck('user');
        foreach ($students as $student) {
            $student->notify(new LiveClassScheduled($liveClass));
        }

        return back()->with('status', 'Live class scheduled — students notified.');
    }

    public function update(LiveClassRequest $request, Cohort $cohort, LiveClass $liveClass): RedirectResponse
    {
        $this->authorizeCohortAccess($request, $cohort);
        abort_unless($liveClass->cohort_id === $cohort->id, 404);

        $liveClass->update($request->validated());

        return back()->with('status', 'Live class updated.');
    }

    public function destroy(Request $request, Cohort $cohort, LiveClass $liveClass): RedirectResponse
    {
        $this->authorizeCohortAccess($request, $cohort);
        abort_unless($liveClass->cohort_id === $cohort->id, 404);

        $liveClass->delete();

        return back()->with('status', 'Live class removed.');
    }

    public function attendance(Request $request, Cohort $cohort, LiveClass $liveClass): Response
    {
        $this->authorizeCohortAccess($request, $cohort);
        abort_unless($liveClass->cohort_id === $cohort->id, 404);

        $enrolledStudents = $cohort->enrollments()->whereIn('status', ['active', 'completed'])->with('user')->get();
        $records = $liveClass->attendanceRecords()->get()->keyBy('user_id');

        return Inertia::render('Admin/LiveClasses/Attendance', [
            'cohort' => ['id' => $cohort->id, 'name' => $cohort->name],
            'liveClass' => ['id' => $liveClass->id, 'title' => $liveClass->title],
            'students' => $enrolledStudents->map(fn ($e) => [
                'user_id' => $e->user->id,
                'name' => $e->user->name,
                'status' => $records[$e->user->id]->status ?? 'pending',
                'verification_source' => $records[$e->user->id]->verification_source ?? null,
                'clicked_join' => $records[$e->user->id]->joined_at !== null,
            ]),
        ]);
    }

    public function markAttendance(Request $request, LiveClass $liveClass): RedirectResponse
    {
        $this->authorizeCohortAccess($request, $liveClass->cohort);

        $validated = $request->validate([
            'records' => ['required', 'array'],
            'records.*.user_id' => ['required', 'exists:users,id'],
            'records.*.status' => ['required', 'in:present,absent,late,excused'],
        ]);

        foreach ($validated['records'] as $record) {
            AttendanceRecord::query()->updateOrCreate(
                ['live_class_id' => $liveClass->id, 'user_id' => $record['user_id']],
                [
                    'status' => $record['status'],
                    'verification_source' => $request->user()->hasRole(Role::INSTRUCTOR) ? 'instructor_verified' : 'admin_verified',
                    'verified_by' => $request->user()->id,
                ]
            );
        }

        return back()->with('status', 'Attendance saved.');
    }

    private function authorizeCohortAccess(Request $request, Cohort $cohort): void
    {
        $user = $request->user();
        $allowed = $user->hasPermission('cohorts.manage')
            || $cohort->instructors()->where('users.id', $user->id)->exists();

        abort_unless($allowed, 403);
    }
}
