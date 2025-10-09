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
        Schema::create('menu_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('meal_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(1); // Order of meals in menu
            $table->boolean('is_featured')->default(false);
            $table->decimal('special_price', 8, 2)->nullable(); // Override meal price
            $table->integer('availability_count')->nullable(); // Limit per week
            $table->integer('sold_count')->default(0);
            $table->timestamps();

            // Unique constraint to prevent duplicate meal in same menu
            $table->unique(['weekly_menu_id', 'meal_id']);
            $table->index(['weekly_menu_id', 'position']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_meals');
    }
};
