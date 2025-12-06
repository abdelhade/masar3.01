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
        Schema::table('employee_leave_balances', function (Blueprint $table) {
            $table->decimal('max_monthly_days', 6, 2)->nullable()->after('pending_days');
            $table->json('monthly_used_days')->nullable()->after('max_monthly_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_balances', function (Blueprint $table) {
            $table->dropColumn(['max_monthly_days', 'monthly_used_days']);
        });
    }
};
