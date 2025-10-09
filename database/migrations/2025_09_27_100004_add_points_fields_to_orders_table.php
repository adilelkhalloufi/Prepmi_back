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
        Schema::table('orders', function (Blueprint $table) {
            // Points and rewards tracking
            $table->integer('points_earned')->default(0)->after('total_price'); // Points earned from this order
            $table->boolean('reward_used')->default(false)->after('points_earned'); // Whether a reward was applied
            $table->decimal('reward_discount_amount', 8, 2)->default(0)->after('reward_used'); // Discount amount from reward (49 MAD)
            $table->foreignId('reward_id')->nullable()->constrained('rewards')->onDelete('set null')->after('reward_discount_amount'); // Which reward was used

            // Order totals breakdown
            $table->decimal('subtotal', 10, 2)->nullable()->after('reward_id'); // Order subtotal before discounts
            $table->decimal('discount_total', 10, 2)->default(0)->after('subtotal'); // Total discounts applied
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('discount_total'); // Delivery charges
            $table->decimal('tax_amount', 8, 2)->default(0)->after('delivery_fee'); // Tax amount if applicable

            // Points calculation metadata
            $table->json('points_breakdown')->nullable()->after('tax_amount'); // Detailed breakdown of how points were calculated
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reward_id');
            $table->dropColumn([
                'points_earned',
                'reward_used',
                'reward_discount_amount',
                'subtotal',
                'discount_total',
                'delivery_fee',
                'tax_amount',
                'points_breakdown',
            ]);
        });
    }
};
