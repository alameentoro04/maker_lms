<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Phase 2 certificates table was deliberately minimal (public-page
     * fields only). Real issuance needs to link back to the enrollment/exam
     * attempt it came from — added here as new columns, per that migration's
     * own note, not by editing it.
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('enrollment_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('exam_attempt_id')->nullable()->after('enrollment_id')->constrained()->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->after('exam_attempt_id')->constrained('users')->nullOnDelete(); // null = auto-issued
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enrollment_id');
            $table->dropConstrainedForeignId('exam_attempt_id');
            $table->dropConstrainedForeignId('issued_by');
        });
    }
};
