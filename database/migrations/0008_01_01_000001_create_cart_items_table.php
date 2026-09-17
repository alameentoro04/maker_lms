<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** A package bundles specific cohorts at one price — its own checkout item, not a course. */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('NGN');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('package_cohort', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['package_id', 'cohort_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_cohort');
        Schema::dropIfExists('packages');
    }
};
