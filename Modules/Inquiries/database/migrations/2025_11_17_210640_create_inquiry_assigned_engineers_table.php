<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_assigned_engineers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')
                ->constrained('inquiries')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // منع تكرار نفس اليوزر لنفس الاستفسار
            $table->unique(['inquiry_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_assigned_engineers');
    }
};
