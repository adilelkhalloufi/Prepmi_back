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
        Schema::create('subscription_weekly_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('weekly_menu_id')->constrained('weekly_menus')->onDelete('cascade');
            
            // Week identification
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->integer('week_number')->comment('Week number within the subscription (1-4 for monthly)');
            
            // Selection status
            $table->enum('status', ['pending', 'confirmed', 'locked', 'delivered'])->default('pending');
            $table->timestamp('locked_at')->nullable()->comment('When selection was locked (48h before delivery)');
            $table->timestamp('confirmed_at')->nullable()->comment('When user confirmed their selection');
            
            // Delivery information for this week
            $table->date('scheduled_delivery_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Additional notes
            $table->text('delivery_notes')->nullable();
            $table->text('special_instructions')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['subscription_id', 'week_start_date']);
            $table->index(['subscription_id', 'status']);
            $table->index('week_start_date');
            $table->index('scheduled_delivery_date');
            
            // Ensure one selection per subscription per week
            $table->unique(['subscription_id', 'week_start_date']);
        });
        
        // Pivot table for selected meals in each weekly selection
        Schema::create('subscription_weekly_selection_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_weekly_selection_id')
                ->constrained('subscription_weekly_selections')
                ->onDelete('cascade');
            $table->foreignId('meal_id')->constrained()->onDelete('cascade');
            
            // Selection details
            $table->integer('quantity')->default(1);
            $table->integer('position')->nullable()->comment('Order of meal in selection');
            
            // Pricing snapshot (locked at selection time)
            $table->decimal('price_at_selection', 8, 2)->nullable();
            
            // Track changes
            $table->timestamp('selected_at')->nullable();
            $table->timestamp('modified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('subscription_weekly_selection_id', 'sws_meals_selection_idx');
            $table->index('meal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_weekly_selection_meals');
        Schema::dropIfExists('subscription_weekly_selections');
    }
};
