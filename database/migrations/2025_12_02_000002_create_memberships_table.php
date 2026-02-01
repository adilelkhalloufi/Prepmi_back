<?php

use App\enum\MembershipStatus;
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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('membership_plan_id')->nullable();
            $table->string('status')->default(MembershipStatus::PENDING->value); // active, inactive, frozen, cancelled, pending
            $table->date('started_at')->nullable(); // When membership became active
            $table->date('ends_at')->nullable(); // When membership ends (if cancelled)
            $table->date('next_billing_date')->nullable(); // Next automatic charge date
            $table->decimal('current_monthly_fee', 10, 2); // Store fee at time of purchase
            $table->decimal('discount_percentage', 5, 2)->default(0); // Store discount % at time of purchase
            $table->integer('delivery_slots_available')->default(3); // Current available slots
            $table->boolean('has_received_monthly_desserts')->default(false); // Track if desserts given this month
            $table->date('last_desserts_received_at')->nullable(); // Last date desserts were auto-added
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            // Ensure one active membership per user
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
