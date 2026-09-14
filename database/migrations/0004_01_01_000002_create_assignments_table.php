<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An assignment is a 1:1 extension of a lesson with type='assignment' —
     * it reuses the lesson's title/order/publication in the curriculum
     * rather than duplicating them. max_submissions is stored for a future
     * attempt-limit feature but is NOT enforced yet beyond the simpler
     * allow_resubmission flag — see README "Known gaps".
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('instructions');
            $table->dateTime('due_at')->nullable();
            $table->json('allowed_file_types')->nullable(); // e.g. ["pdf","jpg","png"]
            $table->unsignedInteger('max_file_size_kb')->default(10240);
            $table->unsignedInteger('max_submissions')->default(1);
            $table->boolean('allow_resubmission')->default(false);
            $table->unsignedInteger('passing_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
