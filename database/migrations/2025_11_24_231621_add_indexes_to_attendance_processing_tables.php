<?php

declare(strict_types=1);

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
        // Add indexes to attendance_processings table
        Schema::table('attendance_processings', function (Blueprint $table) {
            // Index for finding overlapping processings efficiently
            $table->index(['employee_id', 'period_start', 'period_end'], 'idx_employee_period');
            
            // Index for filtering by type and status
            $table->index(['type', 'status'], 'idx_type_status');
            
            // Index for department processing queries
            $table->index(['department_id', 'period_start', 'period_end'], 'idx_department_period');
        });

        // Add indexes to attendance_processing_details table
        Schema::table('attendance_processing_details', function (Blueprint $table) {
            // Index for finding details by processing and date
            $table->index(['attendance_processing_id', 'attendance_date'], 'idx_processing_date');
            
            // Index for employee date queries
            $table->index(['employee_id', 'attendance_date'], 'idx_employee_date');
            
            // Index for department date queries
            $table->index(['department_id', 'attendance_date'], 'idx_department_date');
        });

        // Add indexes to attendances table for better performance
        Schema::table('attendances', function (Blueprint $table) {
            // Index for finding attendances by employee and date range
            $table->index(['employee_id', 'date'], 'idx_attendances_employee_date');
            
            // Index for date range queries
            $table->index('date', 'idx_attendances_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_processings', function (Blueprint $table) {
            $table->dropIndex('idx_employee_period');
            $table->dropIndex('idx_type_status');
            $table->dropIndex('idx_department_period');
        });

        Schema::table('attendance_processing_details', function (Blueprint $table) {
            $table->dropIndex('idx_processing_date');
            $table->dropIndex('idx_employee_date');
            $table->dropIndex('idx_department_date');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_employee_date');
            $table->dropIndex('idx_attendances_date');
        });
    }
};
