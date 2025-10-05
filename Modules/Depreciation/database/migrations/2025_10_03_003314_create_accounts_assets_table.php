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
        Schema::create('accounts_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acc_head_id')->unique(); // One-to-one relationship with AccHead
            
            // Asset Information
            $table->string('asset_name')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->integer('useful_life_years')->nullable();
            
            // Depreciation Settings
            $table->enum('depreciation_method', ['straight_line', 'double_declining', 'sum_of_years'])->default('straight_line');
            $table->decimal('annual_depreciation', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->date('depreciation_start_date')->nullable();
            $table->date('last_depreciation_date')->nullable();
            
            // Related Accounts
            $table->unsignedBigInteger('depreciation_account_id')->nullable(); // حساب مجمع الإهلاك
            $table->unsignedBigInteger('expense_account_id')->nullable(); // حساب مصروف الإهلاك
            
            // Status and Notes
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('acc_head_id')->references('id')->on('acc_head')->onDelete('cascade');
            $table->foreign('depreciation_account_id')->references('id')->on('acc_head')->onDelete('set null');
            $table->foreign('expense_account_id')->references('id')->on('acc_head')->onDelete('set null');
            
            // Indexes for performance
            $table->index('acc_head_id');
            $table->index('is_active');
            $table->index('depreciation_start_date');
            $table->index('last_depreciation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_assets');
    }
};
