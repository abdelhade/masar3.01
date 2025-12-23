<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إضافة indexes محسّنة لإعادة حساب متوسط التكلفة
     */
    public function up(): void
    {
        Schema::table('operation_items', function (Blueprint $table) {
            // Index للاستعلامات المستخدمة في إعادة حساب average_cost
            // (item_id, is_stock, pro_tybe) - للفلترة السريعة
            if (!$this->indexExists('operation_items', 'idx_operation_items_cost_calc')) {
                $table->index(['item_id', 'is_stock', 'pro_tybe'], 'idx_operation_items_cost_calc');
            }
            
            // Index للاستعلامات التي تستخدم pro_id
            if (!$this->indexExists('operation_items', 'idx_operation_items_pro_id')) {
                $table->index('pro_id', 'idx_operation_items_pro_id');
            }
        });

        Schema::table('operhead', function (Blueprint $table) {
            // Index مركب للاستعلامات المستخدمة في إعادة الحساب
            // (pro_date, isdeleted, pro_type) - للفلترة السريعة
            if (!$this->indexExists('operhead', 'idx_operhead_recalc')) {
                $table->index(['pro_date', 'isdeleted', 'pro_type'], 'idx_operhead_recalc');
            }
        });

        Schema::table('items', function (Blueprint $table) {
            // Index على average_cost للاستعلامات السريعة (اختياري)
            if (!$this->indexExists('items', 'idx_items_average_cost')) {
                $table->index('average_cost', 'idx_items_average_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operation_items', function (Blueprint $table) {
            $table->dropIndex('idx_operation_items_cost_calc');
            $table->dropIndex('idx_operation_items_pro_id');
        });

        Schema::table('operhead', function (Blueprint $table) {
            $table->dropIndex('idx_operhead_recalc');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_average_cost');
        });
    }

    /**
     * التحقق من وجود index (database-agnostic)
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        try {
            if ($driver === 'sqlite') {
                // SQLite: Check using pragma index_list
                $indexes = $connection->select("PRAGMA index_list({$table})");
                foreach ($indexes as $idx) {
                    if ($idx->name === $index) {
                        return true;
                    }
                }

                return false;
            } elseif ($driver === 'mysql') {
                // MySQL: Use information_schema
                $databaseName = $connection->getDatabaseName();
                $result = $connection->selectOne(
                    'SELECT COUNT(*) as count 
                     FROM information_schema.statistics 
                     WHERE table_schema = ? 
                     AND table_name = ? 
                     AND index_name = ?',
                    [$databaseName, $table, $index]
                );

                return $result->count > 0;
            } else {
                // For other databases, assume index doesn't exist (safe default)
                return false;
            }
        } catch (\Exception $e) {
            // If check fails, assume index doesn't exist
            return false;
        }
    }
};

