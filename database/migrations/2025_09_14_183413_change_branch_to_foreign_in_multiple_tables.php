<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::table('operhead', function (Blueprint $table) {
            // لو موجودة
            if (Schema::hasColumn('operhead', 'branch')) {
                $table->dropColumn('branch');
            }

            if (Schema::hasColumn('operhead', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });

        $tables = [
            'clients',
            'settings',
            'items',
            'units',
            'journal_types',
            'cost_centers',
            'pro_types',
            'operhead',
            'journal_heads',
            'journal_details',
            'operation_items',
            'acc_head'
        ];

        foreach ($tables as $tableName) {
            // Check if table exists before trying to modify it
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                if (Schema::hasColumn($tableName, 'branch')) {
                    // For SQLite, we need to drop the index first
                    if ($tableName === 'acc_head') {
                        $table->dropIndex('acc_head_branch_index');
                    }
                    $table->dropColumn('branch');
                }
                    
                    // Only add branch_id if it doesn't exist
                    if (!Schema::hasColumn($tableName, 'branch_id')) {
                        $table->foreignId('branch_id')
                            ->nullable()
                            ->default(1)
                            ->constrained('branches')
                            ->nullOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'clients',
            'settings',
            'items',
            'units',
            'journal_types',
            'barcodes',
            'cost_centers',
            'pro_types',
            'operhead',
            'journal_heads',
            'journal_details',
            'operation_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    $table->dropForeign([$tableName . '_branch_id_foreign']);
                    $table->dropColumn('branch_id');
                }

                $table->integer('branch')->default(1);
            });
        }
    }
};
