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
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('short_description')->nullable();

            // Pricing
            $table->decimal('price_per_meal', 8, 2);
            $table->integer('meals_per_week');
            $table->enum('delivery_frequency', ['weekly', 'biweekly', 'monthly'])->default('weekly');

            // Commitment
            $table->integer('min_weeks_commitment')->default(0);
            $table->integer('max_weeks_commitment')->nullable();

            // Additional fees
            $table->decimal('setup_fee', 8, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->boolean('is_free_shipping')->default(false);

            // Trial
            $table->integer('trial_period_days')->default(0);
            $table->boolean('is_trial_available')->default(false);

            // Discount
            $table->decimal('discount_percentage', 5, 2)->default(0);

            // Features and restrictions
            $table->json('features')->nullable();
            $table->json('restrictions')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Terms
            $table->text('terms_and_conditions')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'is_featured', 'sort_order']);
            $table->index('slug');
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
