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
        Schema::table('users', function (Blueprint $table) {
            // Loyalty points system fields
            $table->integer('loyalty_points')->default(0)->after('default_address_id'); // Current available points balance
            $table->integer('total_points_earned')->default(0)->after('loyalty_points'); // Lifetime points earned
            $table->integer('total_points_redeemed')->default(0)->after('total_points_earned'); // Lifetime points spent
            $table->integer('total_rewards_earned')->default(0)->after('total_points_redeemed'); // Number of free meals earned
            $table->integer('total_rewards_used')->default(0)->after('total_rewards_earned'); // Number of free meals used

            // Gamification fields
            $table->timestamp('last_reward_earned_at')->nullable()->after('total_rewards_used'); // When last reward was earned
            $table->json('badges')->nullable()->after('last_reward_earned_at'); // Achievement badges
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_points',
                'total_points_earned',
                'total_points_redeemed',
                'total_rewards_earned',
                'total_rewards_used',
                'last_reward_earned_at',
                'badges',
            ]);
        });
    }
};
