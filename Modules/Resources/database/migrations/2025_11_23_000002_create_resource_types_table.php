<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_category_id')->constrained('resource_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_types');
    }
};

