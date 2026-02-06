<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_items', function (Blueprint $table) {

            // إضافة العمود الجديد فقط لو مش موجود
            if (!Schema::hasColumn('work_items', 'category_id')) {
                $table->foreignId('category_id')
                      ->nullable()
                      ->constrained('work_item_categories')
                      ->nullOnDelete();
            }
        });

        // حذف الـ foreign key constraint أولاً في schema منفصل
        Schema::table('work_items', function (Blueprint $table) {
            if (Schema::hasColumn('work_items', 'predecessor_id')) {
                $table->dropForeign(['predecessor_id']);
            }
        });

        // ثم حذف الأعمدة
        Schema::table('work_items', function (Blueprint $table) {
            $columns = [
                'total_quantity',
                'expected_quantity_per_day',
                'duration',
                'predecessor_id',
                'lag',
                'shift'
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('work_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_items', function (Blueprint $table) {
            // يمكن إضافة الأعمدة القديمة مرة أخرى إذا لزم الأمر
        });
    }
};
