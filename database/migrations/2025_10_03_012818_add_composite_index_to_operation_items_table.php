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
        Schema::table('operation_items', function (Blueprint $table) {
            // Add composite index for performance optimization
            // This index will speed up queries that filter by item_id, unit_id, and detail_store
            $table->index(['item_id', 'unit_id', 'detail_store'], 'operation_items_item_unit_store_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operation_items', function (Blueprint $table) {
            $table->dropIndex('operation_items_item_unit_store_idx');
        });
    }
};
