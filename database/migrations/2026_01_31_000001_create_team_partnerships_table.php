<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_partnerships', function (Blueprint $table) {
            $table->id();
                $table->string('job_title')->nullable();
                $table->string('email');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('company_name');
                $table->string('company_website')->nullable();
                $table->string('partnership_type');
                $table->string('team_members_per_week');
                $table->json('products_interested');
                $table->string('heard_about_us')->nullable();
                $table->boolean('accept_terms');
                $table->boolean('accept_communications');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_partnerships');
    }
};
