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
        Schema::table('operhead', function (Blueprint $table) {
            $table->decimal('vat_percentage', 5, 2)->default(0)->after('fat_plus_per')->comment('نسبة ضريبة القيمة المضافة');
            $table->decimal('vat_value', 15, 2)->default(0)->after('vat_percentage')->comment('قيمة ضريبة القيمة المضافة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            $table->dropColumn(['vat_percentage', 'vat_value']);
        });
    }
};
