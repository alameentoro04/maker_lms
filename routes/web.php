<?php

use App\Http\Controllers\Admin\AssignmentController as AdminAssignmentController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CohortController as AdminCohortController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\ManualPaymentController as AdminManualPaymentController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Instructor\AssignmentController as InstructorAssignmentController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\SubmissionController as InstructorSubmissionController;
use App\Http\Controllers\Public\CertificateVerificationController;
use App\Http\Controllers\Public\CohortController;
use App\Http\Controllers\Public\CourseController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InstructorController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ShowcaseController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\BankTransferController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\VideoStreamController;
use App\Http\Controllers\Webhooks\FlutterwaveWebhookController;
use App\Http\Controllers\Webhooks\PaystackWebhookController;
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

        Route::get('payments/manual', [AdminManualPaymentController::class, 'index'])->name('payments.manual.index');
        Route::get('payments/manual/{submission}/proof', [AdminManualPaymentController::class, 'downloadProof'])->name('payments.manual.download');
        Route::post('payments/manual/{submission}/approve', [AdminManualPaymentController::class, 'approve'])->name('payments.manual.approve');
        Route::post('payments/manual/{submission}/reject', [AdminManualPaymentController::class, 'reject'])->name('payments.manual.reject');
        // Users, etc. routes land in their respective phases.
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
        Route::put('courses/{course}/modules/{module}/lessons/{lesson}/assignment', [AdminAssignmentController::class, 'upsert'])->name('courses.modules.lessons.assignment');
    });

Route::middleware(['auth', 'verified', 'role:'.Role::INSTRUCTOR])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', InstructorDashboardController::class)->name('dashboard');

        Route::get('assignments', [InstructorAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/{assignment}/submissions', [InstructorSubmissionController::class, 'index'])->name('assignments.submissions');
        Route::post('submissions/{submission}/grade', [InstructorSubmissionController::class, 'grade'])->name('submissions.grade');
    });

Route::middleware(['auth', 'verified', 'role:'.Role::STUDENT])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    });

// The course player is shared across every authenticated role (students learn;
// instructors/admins preview what they built) — CourseAccessService, not route
// middleware, decides who actually sees the content.
Route::middleware(['auth', 'verified'])
    ->prefix('learn')
    ->name('learn.')
    ->group(function () {
        Route::get('{course:slug}', [LearnController::class, 'show'])->name('show');
        Route::get('{course:slug}/lessons/{lesson}', [LearnController::class, 'lesson'])->name('lesson');
        Route::post('lessons/{lesson}/complete', [LearnController::class, 'completeLesson'])->name('lessons.complete');
        Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
        Route::get('submissions/{submission}/download', [StudentAssignmentController::class, 'downloadSubmissionFile'])->name('submissions.download');
        Route::get('video/{reference}/mock-stream', VideoStreamController::class)->middleware('signed')->name('video.mock-stream');
    });

// Checkout is student-only — admins/instructors have no reason to buy a seat.
Route::middleware(['auth', 'verified', 'role:'.Role::STUDENT])
    ->prefix('checkout')
    ->name('checkout.')
    ->group(function () {
        Route::get('{cohort:slug}', [CheckoutController::class, 'create'])->name('create');
        Route::post('{cohort:slug}', [CheckoutController::class, 'store'])->name('store');
        Route::get('callback/{provider}', [CheckoutController::class, 'callback'])->name('callback');
        Route::get('bank-transfer/{order}', [CheckoutController::class, 'showBankTransfer'])->name('bank-transfer');
        Route::post('bank-transfer/{order}/proof', [BankTransferController::class, 'submitProof'])->name('bank-transfer.proof');
    });

// Provider-to-server callbacks — no session, no CSRF token; authenticated by
// signature verification inside each controller instead. Exempted from CSRF
// in bootstrap/app.php.
Route::post('webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');
Route::post('webhooks/flutterwave', FlutterwaveWebhookController::class)->name('webhooks.flutterwave');

require __DIR__.'/auth.php';
