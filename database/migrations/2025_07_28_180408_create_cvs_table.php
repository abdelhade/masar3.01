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
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->required();
            $table->string('email')->nullable();
            $table->string('phone')->required();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('birth_date')->required();
            $table->string('gender')->required();
            $table->string('marital_status')->required();
            $table->string('nationality')->required();
            $table->string('religion')->required();
            $table->string('summary')->nullable();
            $table->string('skills')->nullable();
            $table->string('experience')->nullable();
            $table->string('education')->nullable();
            $table->string('projects')->nullable();
            $table->string('certifications')->nullable();
            $table->string('languages')->nullable();
            $table->string('interests')->nullable();
            $table->string('references')->nullable();
            $table->string('cover_letter')->nullable();
            $table->string('portfolio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};
