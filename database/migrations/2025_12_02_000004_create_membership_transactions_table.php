<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->decimal('amount', 10, 2); // Monthly fee charged
            $table->string('transaction_type')->default('monthly_charge'); // monthly_charge, refund, adjustment
            $table->string('payment_status')->default('pending'); // pending, completed, failed, refunded
            $table->string('payment_method')->nullable(); // credit_card, paypal, etc.
            $table->string('payment_reference')->nullable(); // External payment ID
            $table->date('billing_period_start'); // Start of billing period
            $table->date('billing_period_end'); // End of billing period
            $table->timestamp('charged_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['membership_id', 'billing_period_start']);
            $table->index(['user_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_transactions');
    }
};
