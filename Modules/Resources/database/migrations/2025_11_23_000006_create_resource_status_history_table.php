<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('old_status_id')->nullable()->constrained('resource_statuses')->nullOnDelete();
            $table->foreignId('new_status_id')->constrained('resource_statuses')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['resource_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_status_history');
    }
};

