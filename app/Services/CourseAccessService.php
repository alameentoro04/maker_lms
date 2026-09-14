<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;

/**
 * The single place that decides whether a user can see a course's real
 * content. Every controller that serves lesson content, resources, or video
 * MUST check through here — never re-derive access from payment status,
 * cohort membership, or anything else directly. See spec principle #18:
 * "Never expose unauthorized course content."
 */
class CourseAccessService
{
    public function hasAccessToCourse(User $user, Course $course): bool
    {
        if ($user->hasPermission('courses.manage')) {
            return true;
        }

        if ($course->instructors()->where('users.id', $user->id)->exists()) {
            return true;
        }

        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->get()
            ->contains(fn (Enrollment $e) => $e->grantsAccess());
    }

    public function hasAccessToLesson(User $user, Lesson $lesson): bool
    {
        if ($lesson->is_preview) {
            return true;
        }

        return $this->hasAccessToCourse($user, $lesson->module->course);
    }

    /** The enrollment that grants this user's access to this course, if any — used to record progress against. */
    public function activeEnrollmentFor(User $user, Course $course): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->latest('enrolled_at')
            ->first();
    }
}
