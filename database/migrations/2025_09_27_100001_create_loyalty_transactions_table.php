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
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['earned', 'redeemed']); // Points earned or spent
            $table->integer('points'); // Positive for earned, negative for redeemed
            $table->string('description'); // "Earned from 4-meal box", "Redeemed for free meal"
            $table->json('metadata')->nullable(); // Additional context (box size, reward details, etc.)
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
