<?php
// database/migrations/xxxx_create_user_inquiry_preferences_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_inquiry_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('visible_columns')->nullable();
            $table->json('filters')->nullable();
            $table->string('sort_column')->default('created_at');
            $table->string('sort_direction')->default('desc');
            $table->integer('per_page')->default(25);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_inquiry_preferences');
    }
};
