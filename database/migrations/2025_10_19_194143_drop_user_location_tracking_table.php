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
        Schema::dropIfExists('user_location_tracking');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('user_location_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('tracked_at');
            $table->enum('type', ['login', 'tracking', 'attendance'])->default('tracking');
            $table->text('address')->nullable();
            $table->string('place_id')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'session_id']);
            $table->index('tracked_at');
            $table->index('type');
        });
    }
};
