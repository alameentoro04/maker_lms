<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * video_provider/video_reference are placeholders for the VideoProviderInterface
     * wired up in Phase 4 — a lesson can be created and ordered now even though
     * no video is actually uploadable until that service exists.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('text'); // video, text, mixed, quiz, assignment, resource
            $table->longText('content')->nullable(); // text/mixed body
            $table->string('video_provider')->nullable();
            $table->string('video_reference')->nullable(); // provider-side video id/GUID, not a public URL
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_preview')->default(false); // viewable without enrollment
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
