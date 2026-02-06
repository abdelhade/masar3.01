<?php

declare(strict_types=1);

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
        Schema::table('rentals_units', function (Blueprint $table) {
            // إضافة نوع الوحدة (مبنى أو صنف مخزني)
            if (!Schema::hasColumn('rentals_units', 'unit_type')) {
                $table->string('unit_type')->default('building')->after('id');
            }
            
            // إضافة ربط بجدول الأصناف
            if (!Schema::hasColumn('rentals_units', 'item_id')) {
                $table->foreignId('item_id')->nullable()->after('unit_type')->constrained('items')->onDelete('cascade');
            }

            // تعديل عمود المبنى ليكون اختيارياً
            $table->unsignedBigInteger('building_id')->nullable()->change();
            
            // عمود الطابق أصلاً nullable في الميجريشن الأصلي، لكن للتأكيد
            $table->integer('floor')->nullable()->change();
        });

        Schema::table('rentals_leases', function (Blueprint $table) {
            // إضافة نوع التأجير (يومي، شهري، سنوي)
            if (!Schema::hasColumn('rentals_leases', 'rent_type')) {
                $table->string('rent_type')->default('monthly')->after('rent_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals_leases', function (Blueprint $table) {
            $table->dropColumn('rent_type');
        });

        Schema::table('rentals_units', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn(['unit_type', 'item_id']);
            
            // إرجاع عمود المبنى ليكون إجبارياً (قد يفشل إذا كان هناك بيانات بـ null)
            // لذا سنتركه nullable في الـ down لتجنب المشاكل
            // $table->unsignedBigInteger('building_id')->nullable(false)->change();
        });
    }
};
