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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');

            // Subscription timeline
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->date('next_billing_date');
            $table->date('next_delivery_date');

            // Trial
            $table->timestamp('trial_ends_at')->nullable();

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
            $table->text('delivery_notes')->nullable();
            $table->text('special_instructions')->nullable();

            // Addresses
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses');
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses');

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_billing_date']);
            $table->index(['status', 'next_delivery_date']);
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
