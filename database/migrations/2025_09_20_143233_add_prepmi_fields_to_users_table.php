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
        Schema::table('users', function (Blueprint $table) {
            // Only add columns that don't exist - phone and address already exist
            $table->string('city')->nullable()->after('address');
            $table->string('postal_code')->nullable()->after('city');
            $table->string('country')->nullable()->after('postal_code');
            //ADD last_order_calories, last_order_protein, last_order_fat, last_order_carbs if needed
            $table->unsignedInteger('last_order_calories')->nullable();
            $table->unsignedInteger('last_order_protein')->nullable();
            $table->unsignedInteger('last_order_fat')->nullable();
            $table->unsignedInteger('last_order_carbs')->nullable();
            // We'll add the foreign key constraint later after addresses table is created
            $table->unsignedBigInteger('default_address_id')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'city',
                'postal_code',
                'country',
                'default_address_id',
            ]);
        });
    }
};
