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
        Schema::create('membership_freeze_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->nullable();
            $table->date('frozen_at'); // When freeze started
            $table->date('unfrozen_at')->nullable(); // When freeze ended (null if still frozen)
            $table->integer('freeze_duration_days')->nullable(); // Calculated duration
            $table->string('freeze_reason')->nullable();
            $table->date('next_allowed_freeze_date')->nullable(); // Date when user can freeze again (6 months from this freeze)
            $table->timestamps();

            $table->index(['membership_id', 'frozen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_freeze_history');
    }
};
