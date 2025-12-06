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
        Schema::table('employee_leave_balances', function (Blueprint $table) {
            $table->dropColumn(['accrued_days', 'carried_over_days']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_balances', function (Blueprint $table) {
            $table->decimal('accrued_days', 6, 2)->default(0)->after('opening_balance_days');
            $table->decimal('carried_over_days', 6, 2)->default(0)->after('pending_days');
        });
    }
};
