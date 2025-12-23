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
            $table->decimal('withholding_tax_percentage', 5, 2)->default(0)->after('vat_value')->comment('نسبة خصم المنبع');
            $table->decimal('withholding_tax_value', 15, 2)->default(0)->after('withholding_tax_percentage')->comment('قيمة خصم المنبع');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            $table->dropColumn(['withholding_tax_percentage', 'withholding_tax_value']);
        });
    }
};
