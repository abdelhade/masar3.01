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
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->boolean('deducted_from_salary')->default(false)->after('journal_id');
            $table->unsignedBigInteger('deduction_journal_id')->nullable()->after('deducted_from_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropColumn(['deducted_from_salary', 'deduction_journal_id']);
        });
    }
};
