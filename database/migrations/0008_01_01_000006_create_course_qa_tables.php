<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Public-facing course Q&A (helps convert buyers) — distinct from the cohort-scoped Community discussion, which is enrolled-only. */
    public function up(): void
    {
        Schema::create('course_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        Schema::create('course_question_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('reply');
            $table->boolean('is_instructor_reply')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_question_replies');
        Schema::dropIfExists('course_questions');
    }
};
