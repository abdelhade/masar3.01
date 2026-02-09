<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('project_item_id')->constrained('project_items')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('progress_date');
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('completion_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index('project_item_id');
            $table->index('employee_id');
            $table->index('progress_date');
            $table->index(['project_id', 'progress_date']);
            $table->index(['project_item_id', 'progress_date']);
            $table->index(['employee_id', 'progress_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_progress');
    }
};
