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
        Schema::table('attendance_processing_details', function (Blueprint $table) {
            // Rename and change type: overtime hours to minutes
            $table->integer('attendance_overtime_minutes_count')->default(0)->after('attendance_actual_hours_count');
            $table->dropColumn('attendance_overtime_hours_count');
            
            // Rename and change type: late hours to minutes
            $table->integer('attendance_late_minutes_count')->default(0)->after('attendance_overtime_minutes_count');
            $table->dropColumn('attendance_late_hours_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_processing_details', function (Blueprint $table) {
            // Restore original columns
            $table->decimal('attendance_overtime_hours_count', 10, 2)->default(0)->after('attendance_actual_hours_count');
            $table->dropColumn('attendance_overtime_minutes_count');
            
            $table->decimal('attendance_late_hours_count', 10, 2)->default(0)->after('attendance_overtime_hours_count');
            $table->dropColumn('attendance_late_minutes_count');
        });
    }
};
