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
        Schema::create('membership_perks_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('memberships')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('perk_type'); // discount_applied, free_desserts, delivery_slot_used
            $table->decimal('discount_amount', 10, 2)->nullable(); // Amount saved if discount
            $table->integer('items_quantity')->nullable(); // Number of desserts or items
            $table->date('used_at'); // Date perk was used
            $table->json('perk_details')->nullable(); // Additional details as JSON
            $table->timestamps();
            
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_perks_usage');
    }
};
