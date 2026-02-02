<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * تاريخ الاستلام المتوقع لأوامر الشراء (لحساب التأخر وأفضل الموردين في الالتزام).
     */
    public function up(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            if (! Schema::hasColumn('operhead', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('expected_time')
                    ->comment('Expected delivery date for purchase orders (pro_type 15)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            if (Schema::hasColumn('operhead', 'expected_delivery_date')) {
                $table->dropColumn('expected_delivery_date');
            }
        });
    }
};
