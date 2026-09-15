<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One final exam per cohort. Options are stored as JSON on the question
     * row (not a separate exam_options table) — deliberate simplification;
     * see README. Only auto-gradable types (multiple_choice, true_false) are
     * supported — short-answer/manual-graded exam questions are NOT
     * IMPLEMENTED, documented rather than silently unsupported.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('passing_score')->nullable(); // falls back to platform_settings default
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('attempt_limit')->default(1);
            $table->boolean('randomize_questions')->default(false);
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('multiple_choice'); // multiple_choice, true_false
            $table->text('question');
            $table->json('options'); // [{"text": "...", "is_correct": true}, ...]
            $table->text('explanation')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('points')->default(1);
            $table->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'user_id']);
        });

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('selected_option_index')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
