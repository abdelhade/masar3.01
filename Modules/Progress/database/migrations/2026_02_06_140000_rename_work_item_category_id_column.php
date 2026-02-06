<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the old column exists and new one doesn't
        if (Schema::hasColumn('work_items', 'work_item_category_id') && !Schema::hasColumn('work_items', 'category_id')) {
            Schema::table('work_items', function (Blueprint $table) {
                // Drop the foreign key first
                $table->dropForeign(['work_item_category_id']);
            });
            
            Schema::table('work_items', function (Blueprint $table) {
                // Rename the column
                $table->renameColumn('work_item_category_id', 'category_id');
            });
            
            Schema::table('work_items', function (Blueprint $table) {
                // Re-add the foreign key with new column name
                $table->foreign('category_id')
                      ->references('id')
                      ->on('work_item_categories')
                      ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('work_items', 'category_id') && !Schema::hasColumn('work_items', 'work_item_category_id')) {
            Schema::table('work_items', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
            
            Schema::table('work_items', function (Blueprint $table) {
                $table->renameColumn('category_id', 'work_item_category_id');
            });
            
            Schema::table('work_items', function (Blueprint $table) {
                $table->foreign('work_item_category_id')
                      ->references('id')
                      ->on('work_item_categories')
                      ->onDelete('set null');
            });
        }
    }
};
