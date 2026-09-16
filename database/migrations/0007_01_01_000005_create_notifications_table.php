<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel's standard database-notifications shape. This is the one
     * deliberate polymorphic table in the schema (notifiable_type/id) — every
     * other relation in this codebase is a normal foreign key; this is the
     * single justified exception, per Phase 0's "no polymorphism unless
     * justified" rule, because it's what Laravel's built-in Notification
     * system requires.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
