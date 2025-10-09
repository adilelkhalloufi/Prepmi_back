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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('short_description')->nullable();
            $table->string('image_path')->nullable();
            $table->json('gallery_images')->nullable();

            // Nutritional information
            $table->integer('calories')->nullable();
            $table->decimal('protein', 5, 2)->nullable();
            $table->decimal('carbohydrates', 5, 2)->nullable();
            $table->decimal('fats', 5, 2)->nullable();
            $table->decimal('fiber', 5, 2)->nullable();
            $table->decimal('sodium', 8, 2)->nullable(); // in mg
            $table->decimal('sugar', 5, 2)->nullable();

            // Ingredients and allergens
            $table->json('ingredients')->nullable();
            $table->json('allergens')->nullable();
            $table->text('preparation_instructions')->nullable();
            $table->text('storage_instructions')->nullable();

            // Dietary flags
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->boolean('is_dairy_free')->default(false);
            $table->boolean('is_nut_free')->default(false);
            $table->boolean('is_keto')->default(false);
            $table->boolean('is_paleo')->default(false);
            $table->boolean('is_low_carb')->default(false);
            $table->boolean('is_high_protein')->default(false);

            // Additional properties
            $table->boolean('is_spicy')->default(false);
            $table->integer('spice_level')->nullable(); // 1-5 scale
            $table->integer('prep_time_minutes')->nullable();
            $table->integer('cooking_time_minutes')->nullable();
            $table->integer('difficulty_level')->nullable(); // 1-5 scale
            $table->text('chef_notes')->nullable();

            // Availability
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->boolean('is_active')->default(true);

            // Pricing and specs
            $table->decimal('price', 8, 2);
            $table->decimal('cost_per_serving', 8, 2)->nullable();
            $table->integer('weight_grams')->nullable();
            $table->string('serving_size')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'available_from', 'available_to']);
            $table->index(['is_vegetarian', 'is_vegan', 'is_gluten_free']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
