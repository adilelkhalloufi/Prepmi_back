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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('plan_id')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');

            // Subscription timeline
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->date('next_billing_date');
            $table->date('next_delivery_date');



            // Pause functionality
            $table->timestamp('paused_at')->nullable();
            $table->string('pause_reason')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            // Commitment tracking
            $table->integer('weeks_committed')->default(0);
            $table->integer('weeks_remaining')->default(0);

            // Financial tracking
            $table->decimal('total_amount_paid', 10, 2)->default(0);
            $table->integer('meals_delivered')->default(0);

            // Instructions and notes
            $table->string('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('special_instructions')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
