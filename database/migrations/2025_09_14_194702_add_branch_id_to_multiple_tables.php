<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'projects',
            'shifts',
            'departments',
            'employees',
            'leaves',
            'contracts',
            'attendances',
            'pos_shifts',
            'cvs',
            'payroll_runs',
            'payroll_entries',
            'production_orders',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'projects',
            'shifts',
            'departments',
            'employees',
            'leaves',
            'contracts',
            'attendances',
            'pos_shifts',
            'cvs',
            'payroll_runs',
            'payroll_entries',
            'production_orders',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign([$tableName . '_branch_id_foreign']);
                    $table->dropColumn('branch_id');
                });
            }
        }
    }
};
