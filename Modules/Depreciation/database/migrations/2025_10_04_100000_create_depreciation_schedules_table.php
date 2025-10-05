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
        Schema::create('depreciation_schedules', function (Blueprint $table) {
            $table->id();
            
            // Asset reference
            $table->unsignedBigInteger('account_asset_id');
            $table->foreign('account_asset_id')->references('id')->on('accounts_assets')->onDelete('cascade');
            
            // Schedule details
            $table->integer('year_number')->comment('السنة في جدول الإهلاك');
            $table->date('period_start_date')->comment('تاريخ بداية الفترة');
            $table->date('period_end_date')->comment('تاريخ نهاية الفترة');
            
            // Financial amounts
            $table->decimal('beginning_book_value', 15, 2)->comment('القيمة الدفترية في البداية');
            $table->decimal('annual_depreciation', 15, 2)->comment('إهلاك السنة');
            $table->decimal('accumulated_depreciation', 15, 2)->comment('الإهلاك المتراكم');
            $table->decimal('ending_book_value', 15, 2)->comment('القيمة الدفترية في النهاية');
            $table->decimal('depreciation_percentage', 5, 2)->comment('نسبة الإهلاك %');
            
            // Status
            $table->enum('status', ['planned', 'processed', 'adjusted'])->default('planned');
            $table->date('actual_process_date')->nullable();
            $table->decimal('actual_amount', 15, 2)->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['account_asset_id', 'year_number']);
            $table->unique(['account_asset_id', 'year_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciation_schedules');
    }
};