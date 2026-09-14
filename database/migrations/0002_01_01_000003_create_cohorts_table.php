<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Graphic Design — Cohort 1"
            $table->string('slug')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('enrollment_opens_at')->nullable();
            $table->dateTime('enrollment_closes_at')->nullable();
            $table->unsignedInteger('capacity')->default(30);
            $table->string('live_platform')->default('google_meet');
            $table->string('learning_model')->default('recorded_and_live'); // recorded, live, recorded_and_live
            // Draft, Upcoming, Enrollment Open, Enrollment Closed, In Progress, Completed, Archived, Cancelled
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cohort_instructor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cohort_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_instructor');
        Schema::dropIfExists('cohorts');
    }
};
