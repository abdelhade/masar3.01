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
        Schema::table('employee_deductions_rewards', function (Blueprint $table) {
            // Add foreign key constraint with shorter name to avoid MySQL 64 character limit
            $table->foreign('flexible_salary_processing_id', 'emp_ded_rew_flex_sal_proc_fk')
                ->references('id')
                ->on('flexible_salary_processings')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_deductions_rewards', function (Blueprint $table) {
            $table->dropForeign('emp_ded_rew_flex_sal_proc_fk');
        });
    }
};
