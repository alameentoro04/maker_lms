<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drives every configurable business rule referenced in the spec:
        // default exam passing score, cohort defaults, certificate requirements, etc.
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group'); // exams, cohorts, certificates, payments, general
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->timestamps();
            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
