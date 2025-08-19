<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->unsignedSmallInteger('year')->index();
            $table->decimal('opening_balance_days', 6, 2)->default(0);
            $table->decimal('accrued_days', 6, 2)->default(0); // المتراكم خلال السنة
            $table->decimal('used_days', 6, 2)->default(0);
            $table->decimal('pending_days', 6, 2)->default(0);
            $table->decimal('carried_over_days', 6, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique index على (employee_id, leave_type_id, year)
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
