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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Cancellation deadline (48 hours before delivery as per NewNeeds.md)
            $table->timestamp('cancellation_deadline')->nullable()->after('next_delivery_date');

            // Enhanced pause functionality
            $table->timestamp('pause_start_date')->nullable()->after('pause_reason');
            $table->timestamp('pause_end_date')->nullable()->after('pause_start_date');
            $table->integer('max_pause_weeks')->default(4)->after('pause_end_date'); // Maximum weeks a subscription can be paused
            $table->integer('paused_weeks_used')->default(0)->after('max_pause_weeks'); // Track how many weeks have been paused

            // Flexible delivery options
            $table->json('preferred_delivery_days')->nullable()->after('paused_weeks_used'); // Array of preferred delivery days
            $table->json('delivery_restrictions')->nullable()->after('preferred_delivery_days'); // Special delivery notes/restrictions

            // Auto-renewal settings
            $table->boolean('auto_renew')->default(true)->after('delivery_restrictions');
            $table->timestamp('auto_renew_disabled_at')->nullable()->after('auto_renew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'cancellation_deadline',
                'pause_start_date',
                'pause_end_date',
                'max_pause_weeks',
                'paused_weeks_used',
                'preferred_delivery_days',
                'delivery_restrictions',
                'auto_renew',
                'auto_renew_disabled_at',
            ]);
        });
    }
};
