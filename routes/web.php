<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CohortController as AdminCohortController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Public\CertificateVerificationController;
use App\Http\Controllers\Public\CohortController;
use App\Http\Controllers\Public\CourseController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InstructorController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ShowcaseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/cohorts/{cohort:slug}', [CohortController::class, 'show'])->name('cohorts.show');
Route::get('/instructors/{user}', [InstructorController::class, 'show'])->name('instructors.show');
Route::get('/showcase', [ShowcaseController::class, 'index'])->name('showcase.index');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/verify', [CertificateVerificationController::class, 'lookup'])->name('verify.lookup');
Route::get('/verify/{certificateId}', [CertificateVerificationController::class, 'show'])->name('verify');

// Single post-login/register redirect target, resolved per-user in
// User::dashboardRoute() — nothing else in the app should branch on role
// to decide where to send someone.
Route::middleware('auth')->get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware(['auth', 'verified', 'role:'.Role::SUPER_ADMIN.','.Role::ADMIN.','.Role::STAFF])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::resource('cohorts', AdminCohortController::class)->except(['show']);

        Route::get('enrollments', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::post('enrollments', [AdminEnrollmentController::class, 'store'])->name('enrollments.store');
        // Users, payments, etc. routes land in their respective phases.
    });

// Course/module/lesson management is shared with Instructor accounts — CoursePolicy
// restricts instructors to courses they're actually assigned to, so this group only
// needs to gate "not a student/guest", not the fine-grained ownership check.
Route::middleware(['auth', 'verified', 'role:'.Role::SUPER_ADMIN.','.Role::ADMIN.','.Role::STAFF.','.Role::INSTRUCTOR])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('courses', AdminCourseController::class)->except(['show']);
        Route::post('courses/{course}/modules', [AdminModuleController::class, 'store'])->name('courses.modules.store');
        Route::put('courses/{course}/modules/{module}', [AdminModuleController::class, 'update'])->name('courses.modules.update');
        Route::delete('courses/{course}/modules/{module}', [AdminModuleController::class, 'destroy'])->name('courses.modules.destroy');
        Route::post('courses/{course}/modules/{module}/lessons', [AdminLessonController::class, 'store'])->name('courses.modules.lessons.store');
        Route::put('courses/{course}/modules/{module}/lessons/{lesson}', [AdminLessonController::class, 'update'])->name('courses.modules.lessons.update');
        Route::delete('courses/{course}/modules/{module}/lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('courses.modules.lessons.destroy');
    });

Route::middleware(['auth', 'verified', 'role:'.Role::INSTRUCTOR])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', InstructorDashboardController::class)->name('dashboard');
    });

Route::middleware(['auth', 'verified', 'role:'.Role::STUDENT])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    });

require __DIR__.'/auth.php';
