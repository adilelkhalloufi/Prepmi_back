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
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->boolean('free_delivery')->default(false);
            $table->decimal('fixed_discount_amount', 10, 2)->default(0.00);
            $table->boolean('has_premium_access')->default(false);
            $table->decimal('premium_upgrade_fee_min', 10, 2)->default(0.00);
            $table->decimal('premium_upgrade_fee_max', 10, 2)->default(0.00);
            $table->integer('free_freezes_per_period')->default(1);
            $table->integer('freeze_period_months')->default(6);
            $table->boolean('cancellable_anytime')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn(['cancellable_anytime', 'freeze_period_months', 'free_freezes_per_period', 'premium_upgrade_fee_max', 'premium_upgrade_fee_min', 'has_premium_access', 'fixed_discount_amount', 'free_delivery']);
        });
    }
};
