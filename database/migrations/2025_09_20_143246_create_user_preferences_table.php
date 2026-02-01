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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();

            // Dietary restrictions (boolean flags)
            $table->boolean('vegetarian')->default(false);
            $table->boolean('vegan')->default(false);
            $table->boolean('gluten_free')->default(false);
            $table->boolean('dairy_free')->default(false);
            $table->boolean('nut_free')->default(false);
            $table->boolean('keto')->default(false);
            $table->boolean('paleo')->default(false);
            $table->boolean('low_carb')->default(false);
            $table->boolean('high_protein')->default(false);

            // JSON fields for complex data
            $table->json('allergies')->nullable(); // Array of allergens
            $table->json('dislikes')->nullable(); // Array of disliked ingredients

            // Numeric preferences
            $table->integer('max_calories_per_meal')->nullable();
            $table->enum('preferred_portion_size', ['small', 'medium', 'large'])->default('medium');

            // Additional notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
