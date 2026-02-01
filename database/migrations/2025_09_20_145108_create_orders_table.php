<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('num_order')->nullable();
            $table->dateTime('date_order')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('adresse_livrsion')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('plan_id')->nullable();
            $table->foreignId('subscription_id')->nullable();
            $table->string('method_payement')->nullable();
            $table->integer('reward_point')->nullable();
            $table->string('statue')->nullable();
            // Points and rewards tracking
            $table->integer('points_earned')->nullable(); // Points earned from this order
            $table->boolean('reward_used')->nullable(); // Whether a reward was applied
            $table->decimal('reward_discount_amount', 8, 2)->nullable(); // Discount amount from reward (49 MAD)

            // Order totals breakdown
            $table->decimal('subtotal', 10, 2)->nullable(); // Order subtotal before discounts
            $table->decimal('discount_total', 10, 2)->nullable(); // Total discounts applied
            $table->decimal('delivery_fee', 8, 2)->nullable(); // Delivery charges
            $table->decimal('tax_amount', 8, 2)->nullable(); // Tax amount if applicable
            $table->decimal('total_amount', 10, 2)->nullable(); // Final total amount to be paid
            // add size selected by user for all small or larage or medium
            $table->string('size')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
