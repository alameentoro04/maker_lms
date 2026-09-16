<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** cohort_id nullable = general/platform-wide feed; set = cohort-scoped discussion. */
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('community_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        Schema::create('community_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('community_comment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status')->default('pending'); // pending, reviewed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_reports');
        Schema::dropIfExists('community_comments');
        Schema::dropIfExists('community_posts');
    }
};
