<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_project')) {
            Schema::create('employee_project', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['employee_id', 'project_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_project');
    }
};
