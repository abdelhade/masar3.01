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
        Schema::create('work_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('parent_id')->references('id')->on('work_types')->onDelete('cascade');

            $table->index(['parent_id']);
            $table->index(['is_active']);
            $table->index(['parent_id', 'is_active']);

            $table->unique(['name', 'parent_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_types');
    }
};
