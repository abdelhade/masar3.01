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
        Schema::create('employee_deductions_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('attendance_processing_id')->nullable()->constrained('attendance_processings')->onDelete('set null');

            // Create column without foreign key constraint first (table doesn't exist yet)
            // Foreign key will be added in a separate migration after flexible_salary_processings table is created
            $table->unsignedBigInteger('flexible_salary_processing_id')->nullable();

            $table->enum('type', ['deduction', 'reward']);
            $table->string('reason');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('journal_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('employee_id');
            $table->index('date');
            $table->index('type');
            $table->index('attendance_processing_id');
            $table->index('flexible_salary_processing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_deductions_rewards');
    }
};
