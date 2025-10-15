<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add index to barcodes table for faster lookup
        Schema::table('barcodes', function (Blueprint $table) {
            // Index on barcode column for search
            if (!$this->hasIndex('barcodes', 'barcodes_barcode_index')) {
                $table->index('barcode', 'barcodes_barcode_index');
            }
            
            // Composite index for item_id and unit_id
            if (!$this->hasIndex('barcodes', 'barcodes_item_unit_index')) {
                $table->index(['item_id', 'unit_id'], 'barcodes_item_unit_index');
            }
        });

        // Add indexes to item_prices table
        Schema::table('item_prices', function (Blueprint $table) {
            // Composite index for faster price lookups
            if (!$this->hasIndex('item_prices', 'item_prices_item_unit_price_index')) {
                $table->index(['item_id', 'unit_id', 'price_id'], 'item_prices_item_unit_price_index');
            }
        });

        // Add indexes to item_units table
        Schema::table('item_units', function (Blueprint $table) {
            // Composite index for item and unit lookup
            if (!$this->hasIndex('item_units', 'item_units_item_unit_index')) {
                $table->index(['item_id', 'unit_id'], 'item_units_item_unit_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            $table->dropIndex('barcodes_barcode_index');
            $table->dropIndex('barcodes_item_unit_index');
        });

        Schema::table('item_prices', function (Blueprint $table) {
            $table->dropIndex('item_prices_item_unit_price_index');
        });

        Schema::table('item_units', function (Blueprint $table) {
            $table->dropIndex('item_units_item_unit_index');
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            // For MySQL/MariaDB
            if (DB::getDriverName() === 'mysql') {
                $indexes = DB::select("SHOW INDEXES FROM {$table}");
                foreach ($indexes as $index) {
                    if ($index->Key_name === $indexName) {
                        return true;
                    }
                }
            }
            // For SQLite
            elseif (DB::getDriverName() === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list({$table})");
                foreach ($indexes as $index) {
                    if ($index->name === $indexName) {
                        return true;
                    }
                }
            }
            // For PostgreSQL
            elseif (DB::getDriverName() === 'pgsql') {
                $indexes = DB::select("
                    SELECT indexname 
                    FROM pg_indexes 
                    WHERE tablename = ? AND indexname = ?
                ", [$table, $indexName]);
                return count($indexes) > 0;
            }
        } catch (Exception $e) {
            // If we can't check, assume it doesn't exist
            return false;
        }
        
        return false;
    }
};
