<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One order can have several payment attempts (retry after failure, or
     * switch from online to bank transfer) — status lives here, not just on
     * the order, so a failed Paystack attempt doesn't block a later bank
     * transfer against the same order.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // paystack, flutterwave, bank_transfer, manual
            $table->string('reference')->unique(); // our reference sent to the provider
            $table->string('provider_reference')->nullable(); // provider's own transaction id, once known
            $table->string('status')->default('pending'); // pending, verified, failed
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->json('meta')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payments');
    }
};
