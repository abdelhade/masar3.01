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
        Schema::create('attendance_processings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['single', 'multiple', 'department'])->default('single');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_days');
            $table->integer('working_days');
            $table->decimal('total_hours', 10, 2);
            $table->decimal('calculated_salary_for_day', 10, 2)->default(0);
            $table->decimal('calculated_salary_for_hour', 10, 2)->default(0);
            $table->integer('actual_work_days')->default(0);
            $table->decimal('actual_work_hours', 10, 2)->default(0);
            $table->integer('overtime_work_days')->default(0);
            $table->decimal('overtime_work_hours', 10, 2)->default(0);
            $table->integer('absent_days')->default(0);
            $table->decimal('employee_productivity_salary', 10, 2)->default(0);
            $table->decimal('salary_due', 15, 2)->default(0);
            $table->decimal('total_salary', 15, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate processing for same employee and period
            $table->unique(['employee_id', 'type', 'period_start', 'period_end'], 'unique_employee_processing_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_processings');
    }
};
