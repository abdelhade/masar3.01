<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('leave_days_paid', 6, 2)->default(0);
            $table->decimal('leave_days_unpaid', 6, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // مؤشرات للبحث
            $table->index(['payroll_run_id', 'employee_id']);
            $table->index(['employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
