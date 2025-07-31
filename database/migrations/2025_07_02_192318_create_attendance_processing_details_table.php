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
        Schema::create('attendance_processing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_processing_id')->constrained('attendance_processings')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->date('attendance_date');
            $table->string('day_type')->nullable(); // holiday, working_day, overtime_day
            $table->string('attendance_status')->default('absent'); // حضور أو غياب أو إجازة أو إذن
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();
            $table->decimal('working_hours_in_shift', 10, 2)->default(0);
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('attendance_basic_hours_count', 10, 2)->default(0);
            $table->decimal('attendance_actual_hours_count', 10, 2)->default(0);
            $table->decimal('attendance_overtime_hours_count', 10, 2)->default(0);
            $table->decimal('attendance_late_hours_count', 10, 2)->default(0);
            $table->decimal('early_hours', 10, 2)->default(0);
            $table->decimal('attendance_total_hours_count', 10, 2)->default(0);
            $table->decimal('total_due_hourly_salary', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate detail records
            $table->unique(['attendance_processing_id', 'employee_id', 'attendance_date'], 'unique_processing_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_processing_details');
    }
};
