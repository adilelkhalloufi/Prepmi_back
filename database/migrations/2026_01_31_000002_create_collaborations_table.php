<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('social_url_1');
            $table->string('social_url_2')->nullable();
            $table->string('social_url_3')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('country');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborations');
    }
};
