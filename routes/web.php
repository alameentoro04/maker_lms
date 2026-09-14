<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
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
        // Users, courses, cohorts, payments, etc. routes land in their respective phases.
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
