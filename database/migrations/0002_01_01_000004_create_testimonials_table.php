<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Not in the original Phase 0 schema sketch (which starts testimonials
     * from real student data collected later) — added now as a small,
     * admin-editable content table so the homepage isn't hard-coded copy.
     * Seeded rows are placeholder-only; see TestimonialSeeder.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_role')->nullable(); // e.g. "Graphic Design, Cohort 1"
            $table->text('quote');
            $table->string('avatar_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
