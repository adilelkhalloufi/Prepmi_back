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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['free_meal'])->default('free_meal'); // Can expand for other reward types
            $table->decimal('value', 8, 2)->default(49.00); // 49 MAD discount value
            $table->string('title')->default('Repas PrepMe Gratuit'); // Display title
            $table->string('description')->default('Réduction de 49 MAD applicable sur votre prochaine commande'); // Description

            // Status tracking
            $table->boolean('is_used')->default(false);
            $table->timestamp('earned_at'); // When the reward was earned (12 points reached)
            $table->timestamp('expires_at')->nullable(); // Optional expiration date
            $table->timestamp('used_at')->nullable(); // When the reward was redeemed

            // Usage tracking
            $table->foreignId('used_order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->decimal('discount_applied', 8, 2)->nullable(); // Actual discount amount applied

            // Additional metadata
            $table->json('conditions')->nullable(); // Any special conditions for the reward
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_used']);
            $table->index(['user_id', 'earned_at']);
            $table->index(['expires_at', 'is_used']);
            $table->index('used_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
