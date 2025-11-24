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
        Schema::table('attendance_processings', function (Blueprint $table) {
            // Rename and change type: overtime hours to minutes
            $table->integer('overtime_work_minutes')->default(0)->after('actual_work_hours');
            $table->dropColumn('overtime_work_hours');
            
            // Rename and change type: late hours to minutes
            $table->integer('total_late_minutes')->default(0)->after('overtime_work_minutes');
            $table->dropColumn('total_late_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_processings', function (Blueprint $table) {
            // Restore original columns
            $table->decimal('overtime_work_hours', 10, 2)->default(0)->after('actual_work_hours');
            $table->dropColumn('overtime_work_minutes');
            
            $table->decimal('total_late_hours', 10, 2)->default(0)->after('overtime_work_hours');
            $table->dropColumn('total_late_minutes');
        });
    }
};
