<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * order_items
         */

        if (!Schema::hasColumn('order_items', 'cohort_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('cohort_id')
                    ->nullable()
                    ->after('order_id');
            });
        }

        if (!Schema::hasColumn('order_items', 'package_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('package_id')
                    ->nullable()
                    ->after('cohort_id');
            });
        }

        // Ensure cohort_id foreign key exists.
        $orderItemForeignKeys = collect(
            Schema::getForeignKeys('order_items')
        );

        if (
            Schema::hasColumn('order_items', 'cohort_id') &&
            !$orderItemForeignKeys->contains(
                fn ($fk) => in_array('cohort_id', $fk['columns'], true)
            )
        ) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreign('cohort_id')
                    ->references('id')
                    ->on('cohorts')
                    ->nullOnDelete();
            });
        }

        // Ensure package_id foreign key exists.
        $orderItemForeignKeys = collect(
            Schema::getForeignKeys('order_items')
        );

        if (
            Schema::hasColumn('order_items', 'package_id') &&
            !$orderItemForeignKeys->contains(
                fn ($fk) => in_array('package_id', $fk['columns'], true)
            )
        ) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreign('package_id')
                    ->references('id')
                    ->on('packages')
                    ->nullOnDelete();
            });
        }

        /*
         * orders
         */

        if (!Schema::hasColumn('orders', 'coupon_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('coupon_id')
                    ->nullable()
                    ->after('cohort_id');
            });
        }

        if (!Schema::hasColumn('orders', 'discount_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedInteger('discount_amount')
                    ->default(0)
                    ->after('coupon_id');
            });
        }

        /*
         * course_id is now optional because an order can contain
         * multiple cohorts/courses or a package.
         */

        $orderForeignKeys = collect(
            Schema::getForeignKeys('orders')
        );

        $courseForeignKey = $orderForeignKeys->first(
            fn ($fk) => in_array('course_id', $fk['columns'], true)
        );

        if ($courseForeignKey) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['course_id']);
            });
        }

        DB::statement(
            'ALTER TABLE orders MODIFY course_id BIGINT UNSIGNED NULL'
        );

        $orderForeignKeys = collect(
            Schema::getForeignKeys('orders')
        );

        $courseForeignKey = $orderForeignKeys->first(
            fn ($fk) => in_array('course_id', $fk['columns'], true)
        );

        if (!$courseForeignKey) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('course_id')
                    ->references('id')
                    ->on('courses')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // This migration has already partially run in the database,
        // so rollback is intentionally conservative.

        if (Schema::hasColumn('orders', 'course_id')) {
            $orderForeignKeys = collect(
                Schema::getForeignKeys('orders')
            );

            if ($orderForeignKeys->contains(
                fn ($fk) => in_array('course_id', $fk['columns'], true)
            )) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropForeign(['course_id']);
                });
            }

            DB::statement(
                'ALTER TABLE orders MODIFY course_id BIGINT UNSIGNED NULL'
            );

            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('course_id')
                    ->references('id')
                    ->on('courses')
                    ->cascadeOnDelete();
            });
        }
    }
};