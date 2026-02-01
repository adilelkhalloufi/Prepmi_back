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
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_name');
            $table->string('slot_type')->default('both'); // membership, normal, both
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_capacity')->default(10);
            $table->integer('current_bookings')->default(0);
            $table->integer('day_of_week')->nullable()->comment('0=Sunday, 1=Monday, ..., 6=Saturday');
            $table->boolean('is_active')->default(true);
            $table->decimal('price_adjustment', 8, 2)->default(0.00)->comment('Additional cost for premium slots');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add delivery_slot_id to deliveries table
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('delivery_slot_id')->nullable()->after('order_id')
                ->nullable();
            $table->index('delivery_slot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['delivery_slot_id']);
            $table->dropColumn('delivery_slot_id');
        });

        Schema::dropIfExists('delivery_slots');
    }
};
