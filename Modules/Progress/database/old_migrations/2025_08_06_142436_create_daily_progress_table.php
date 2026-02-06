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
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('project_item_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('employee_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->date('progress_date');
            $table->decimal('quantity', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_progress');
    }
};
