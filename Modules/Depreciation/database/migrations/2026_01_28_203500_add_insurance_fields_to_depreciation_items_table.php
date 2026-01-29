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
        Schema::table('depreciation_items', function (Blueprint $table) {
            $table->date('insurance_renewal_date')->nullable()->after('notes');
            $table->integer('insurance_notification_days')->default(30)->after('insurance_renewal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depreciation_items', function (Blueprint $table) {
            $table->dropColumn(['insurance_renewal_date', 'insurance_notification_days']);
        });
    }
};
