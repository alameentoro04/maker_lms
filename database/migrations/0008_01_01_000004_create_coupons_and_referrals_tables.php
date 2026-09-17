<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Referral rewards are implemented as single-use coupons rather than a
     * cash wallet/ledger — simpler, and avoids a real-money balance that
     * would need its own withdrawal/audit rules the spec never defined.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('percent'); // percent, fixed
            $table->unsignedInteger('value'); // percent (0-100) or fixed minor-unit amount
            $table->foreignId('issued_to')->nullable()->constrained('users')->nullOnDelete(); // null = general/admin-issued coupon
            $table->string('source')->default('admin'); // admin, referral_referrer, referral_referred
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); // set once the referred user completes a purchase
            $table->string('status')->default('pending'); // pending, rewarded
            $table->timestamps();

            $table->unique('referred_id'); // one referrer credited per referred user, ever
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->unique()->after('phone');
        });
        Schema::table('orders', function (Blueprint $table) {
    $table->foreign('coupon_id')
        ->references('id')
        ->on('coupons')
        ->nullOnDelete();
});
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_code');
        });
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('coupons');
    }
};
