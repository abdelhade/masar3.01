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
        // Update employees table: change finger_print_id from integer to unsignedBigInteger
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('finger_print_id')->nullable()->change();
        });

        // Update attendances table: change employee_attendance_finger_print_id from string to unsignedBigInteger
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_attendance_finger_print_id')->nullable()->change();
        });

        // Add project_code column to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('project_code')->nullable()->after('employee_attendance_finger_print_name');
        });

         // Add project_code column to attendances processing details table
         Schema::table('attendance_processing_details', function (Blueprint $table) {
            $table->string('project_code')->nullable()->after('attendance_date');
        });

        // add indexs (employee_id, attendance_date, project_code) to the  attendance_processing_details tables
        Schema::table('attendance_processing_details', function (Blueprint $table) {
            $table->index(['employee_id', 'attendance_date', 'project_code'], 'idx_attendance_processing_details_employee_date_project_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert employees table: change finger_print_id back to integer
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('finger_print_id')->nullable()->change();
        });

        // Revert attendances table: change employee_attendance_finger_print_id back to string
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('employee_attendance_finger_print_id')->nullable()->change();
        });

        // Remove project_code column from attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('project_code');
        });

        // Remove indexs (employee_id, attendance_date, project_code) from the attendance_processing_details tables
        Schema::table('attendance_processing_details', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_processing_details_employee_date_project_code');
        });

        // Remove project_code column from attendance_processing_details table
        Schema::table('attendance_processing_details', function (Blueprint $table) {
            $table->dropColumn('project_code');
        });
    }
};