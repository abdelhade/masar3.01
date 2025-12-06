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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['accrual_rate_per_month', 'carry_over_limit_days']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->decimal('accrual_rate_per_month', 6, 2)->nullable()->after('max_per_request_days');
            $table->unsignedInteger('carry_over_limit_days')->nullable()->after('accrual_rate_per_month');
        });
    }
};
