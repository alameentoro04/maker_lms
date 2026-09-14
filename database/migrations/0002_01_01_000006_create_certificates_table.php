<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 scope: only what the PUBLIC verification page needs, denormalized
     * on purpose so /verify/{id} never has to join into enrollments/users and
     * risk leaking private student data. Phase 6 (real issuance) adds a
     * separate migration with enrollment_id, revocation workflow, template,
     * etc. — this table gains columns then, it isn't redesigned.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_id')->unique(); // e.g. MKR-GD-2026-000001
            $table->string('holder_name'); // denormalized snapshot at issue time
            $table->string('course_title');
            $table->string('cohort_label')->nullable();
            $table->date('issued_at');
            $table->string('status')->default('active'); // active, revoked
            $table->string('revoked_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
