<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 scope: enough columns to publish and display a course on the
     * public site. Modules/lessons/curriculum management is Phase 3 — those
     * migrations add child tables rather than reshaping this one.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary'); // short catalog-card description
            $table->longText('description'); // full course-page description
            $table->json('objectives')->nullable(); // "what you'll learn" bullet list
            $table->json('requirements')->nullable(); // prerequisites bullet list
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced
            $table->unsignedInteger('duration_weeks')->default(4);
            $table->string('status')->default('draft'); // draft, published, archived
            $table->unsignedInteger('price')->default(0); // minor currency units (kobo)
            $table->string('currency', 3)->default('NGN');
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_instructor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_instructor');
        Schema::dropIfExists('courses');
    }
};
