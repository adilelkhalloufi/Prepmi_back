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
        Schema::table('plans', function (Blueprint $table) {
            // Loyalty points mapping based on NewNeeds.md requirements
            $table->integer('points_value')->default(0)->after('meals_per_week'); // Points earned per box

            // Box pricing from NewNeeds.md for reference
            $table->decimal('box_price', 8, 2)->nullable()->after('points_value'); // Full box price (180, 265, 345, 415 MAD)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'points_value',
                'box_price'
            ]);
        });
    }
};
