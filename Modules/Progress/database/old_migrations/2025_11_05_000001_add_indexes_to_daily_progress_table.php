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
        Schema::table('daily_progress', function (Blueprint $table) {
            // Add individual indexes for common queries
            $table->index('progress_date', 'idx_daily_progress_date');
            $table->index('project_item_id', 'idx_daily_progress_project_item');
            $table->index('project_id', 'idx_daily_progress_project');
            
            // Add composite index for date range queries on specific items
            $table->index(['project_item_id', 'progress_date'], 'idx_daily_progress_item_date');
            
            // Add composite index for project-wide date queries
            $table->index(['project_id', 'progress_date'], 'idx_daily_progress_project_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_progress', function (Blueprint $table) {
            $table->dropIndex('idx_daily_progress_date');
            $table->dropIndex('idx_daily_progress_project_item');
            $table->dropIndex('idx_daily_progress_project');
            $table->dropIndex('idx_daily_progress_item_date');
            $table->dropIndex('idx_daily_progress_project_date');
        });
    }
};

