<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // الأعمدة المطلوب إضافتها
        $columnsToAdd = [
            'total_quantity' => function (Blueprint $table) {
                $table->decimal('total_quantity', 10, 2)->nullable()->after('description');
            },
            'expected_quantity_per_day' => function (Blueprint $table) {
                $table->decimal('expected_quantity_per_day', 10, 2)->nullable()->after('total_quantity');
            },
            'duration' => function (Blueprint $table) {
                $table->integer('duration')->nullable()->after('expected_quantity_per_day');
            },
            'predecessor_id' => function (Blueprint $table) {
                $table->foreignId('predecessor_id')->nullable()->after('duration');
            },
            'lag' => function (Blueprint $table) {
                $table->integer('lag')->default(0)->after('predecessor_id');
            },
            'shift' => function (Blueprint $table) {
                $table->string('shift')->nullable()->after('lag');
            },
        ];

        // إضافة الأعمدة لو مش موجودة
        foreach ($columnsToAdd as $columnName => $callback) {
            if (!Schema::hasColumn('work_items', $columnName)) {
                Schema::table('work_items', $callback);
            }
        }

        // إضافة المفتاح الخارجي لو العمود موجود ومفيش FK قبله
        if (Schema::hasColumn('work_items', 'predecessor_id')) {
            $foreignKeys = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'work_items'
                  AND COLUMN_NAME = 'predecessor_id'
                  AND CONSTRAINT_NAME <> 'PRIMARY'
            ");

            if ($foreignKeys[0]->count == 0) {
                Schema::table('work_items', function (Blueprint $table) {
                    $table->foreign('predecessor_id')
                          ->references('id')
                          ->on('work_items')
                          ->onDelete('set null');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('work_items', function (Blueprint $table) {
            // إزالة المفتاح الخارجي لو موجود
            if (Schema::hasColumn('work_items', 'predecessor_id')) {
                $table->dropForeign(['predecessor_id']);
            }

            // حذف الأعمدة اللي أضفناها
            foreach (['total_quantity', 'expected_quantity_per_day', 'duration', 'predecessor_id', 'lag', 'shift'] as $col) {
                if (Schema::hasColumn('work_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
