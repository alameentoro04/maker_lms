<?php

namespace App\Http\Controllers\Admin;

use App\Actions\EnrollStudentAction;
use App\Exceptions\EnrollmentNotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManualEnrollmentRequest;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Enrollment::class);

        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => Enrollment::query()->with(['user', 'course', 'cohort'])->latest()->get()->map(fn (Enrollment $e) => [
                'id' => $e->id,
                'student' => $e->user->name,
                'course' => $e->course->title,
                'cohort' => $e->cohort?->name,
                'status' => $e->status,
                'enrolled_at' => $e->enrolled_at?->toFormattedDateString(),
                'is_override' => $e->is_override,
            ]),
            'students' => User::query()->where('role_id', Role::query()->where('slug', Role::STUDENT)->value('id'))->get(['id', 'name', 'email']),
            'cohorts' => Cohort::query()->with('course')->get()->map(fn (Cohort $c) => [
                'id' => $c->id,
                'label' => "{$c->course->title} — {$c->name}",
                'status' => $c->statusLabel(),
                'capacity' => $c->capacity,
                'enrolled' => $c->activeEnrollmentCount(),
            ]),
        ]);
    }

    /**
     * "Manual payment confirmation" enrollment path (bank transfer, cash, etc.)
     * — this is the same EnrollStudentAction that Phase 5's Paystack/Flutterwave
     * webhook handlers will call after verifying payment. Only an admin with
     * enrollments.manage can pass override=true.
     */
    public function store(ManualEnrollmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $validated = $request->validated();
        $student = User::query()->findOrFail($validated['user_id']);
        $cohort = Cohort::query()->findOrFail($validated['cohort_id']);

        try {
            (new EnrollStudentAction())->execute(
                student: $student,
                cohort: $cohort,
                actor: $request->user(),
                override: (bool) ($validated['override'] ?? false),
            );
        } catch (EnrollmentNotAllowedException $e) {
            throw ValidationException::withMessages(['cohort_id' => $e->getMessage()]);
        }

        return back()->with('status', "Enrolled {$student->name}.");
    }
}
