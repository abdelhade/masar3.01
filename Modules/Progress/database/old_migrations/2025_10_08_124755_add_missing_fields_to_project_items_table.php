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
        Schema::table('project_items', function (Blueprint $table) {
            // Add predecessor field for dependency management
            if (!Schema::hasColumn('project_items', 'predecessor')) {
                $table->string('predecessor')->nullable()->after('lag');
            }

            // Add dependency type field
            if (!Schema::hasColumn('project_items', 'dependency_type')) {
                $table->enum('dependency_type', ['end_to_start', 'start_to_start'])
                      ->default('end_to_start')
                      ->after('predecessor');
            }

            // Add item order field for sorting
            if (!Schema::hasColumn('project_items', 'item_order')) {
                $table->integer('item_order')->default(0)->after('dependency_type');
            }

            // Add remaining quantity field
            if (!Schema::hasColumn('project_items', 'remaining_quantity')) {
                $table->decimal('remaining_quantity', 12, 2)->default(0)->after('completed_quantity');
            }

            // Add planned end date field
            if (!Schema::hasColumn('project_items', 'planned_end_date')) {
                $table->date('planned_end_date')->nullable()->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            $table->dropColumn([
                'predecessor',
                'dependency_type',
                'item_order',
                'remaining_quantity',
                'planned_end_date'
            ]);
        });
    }
};
