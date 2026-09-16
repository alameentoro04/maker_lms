<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Lets a student submit their own showcase entry — the Phase 2 table only supported admin/seeder-authored entries. */
    public function up(): void
    {
        Schema::table('project_showcases', function (Blueprint $table) {
            $table->foreignId('submitted_by')->nullable()->after('course_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_showcases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by');
        });
    }
};
