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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Premium Membership", "VIP Membership"
            $table->text('description')->nullable();
            $table->decimal('monthly_fee', 10, 2); // Monthly subscription fee
            $table->decimal('discount_percentage', 5, 2)->default(0); // Discount % on orders
            $table->integer('delivery_slots')->default(3); // Number of delivery slots available
            $table->boolean('includes_free_desserts')->default(false); // Auto-add desserts perk
            $table->integer('free_desserts_quantity')->default(2); // Number of free desserts
            $table->json('perks')->nullable(); // Additional perks as JSON
            $table->boolean('is_active')->default(true);
            $table->integer('billing_day_of_month')->default(1); // Day of month for billing
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
