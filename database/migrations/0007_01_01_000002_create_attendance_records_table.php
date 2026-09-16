<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A "joined" LMS click is recorded (verification_source=lms_click) but is
     * NEVER treated as verified attendance on its own — status stays 'pending'
     * until an instructor/admin confirms it. See spec principle: "A Google
     * Meet URL click is not equivalent to verified attendance."
     */
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, present, absent, late, excused
            $table->string('verification_source')->default('lms_click'); // lms_click, instructor_verified, admin_verified
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['live_class_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
