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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('meals_per_week')->default(0);
            $table->decimal('price_per_week', 8, 2)->default(0);
            $table->decimal('price_subscription_per_week', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('points_value')->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->boolean('is_free_shipping')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
