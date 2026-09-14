<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * order_id is intentionally NOT a column here — Phase 5 (Payments) adds it
     * via a new migration once the orders table exists, rather than this one
     * being reshaped. Every enrollment in Phase 3/4 is created through
     * EnrollStudentAction with status set explicitly (manual/admin path);
     * nothing defaults an enrollment to "active" implicitly.
     */
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, active, completed, cancelled, suspended, expired
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('access_starts_at')->nullable();
            $table->timestamp('access_ends_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete(); // admin, if manually enrolled
            $table->boolean('is_override')->default(false); // true if capacity/deadline rules were bypassed
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cohort_id', 'status']);
        });

        Schema::create('enrollment_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_status_history');
        Schema::dropIfExists('enrollments');
    }
};
