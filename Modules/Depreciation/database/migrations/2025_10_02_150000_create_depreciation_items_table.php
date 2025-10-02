<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('depreciation_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('purchase_date');
            $table->decimal('cost', 15, 2);
            $table->integer('useful_life'); // in years
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->decimal('annual_depreciation', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->string('depreciation_method')->default('straight_line');
            $table->unsignedBigInteger('asset_account_id')->nullable();
            $table->unsignedBigInteger('depreciation_account_id')->nullable();
            $table->unsignedBigInteger('expense_account_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('asset_account_id')->references('id')->on('acc_head')->onDelete('cascade');
            $table->foreign('depreciation_account_id')->references('id')->on('acc_head')->onDelete('cascade');
            $table->foreign('expense_account_id')->references('id')->on('acc_head')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depreciation_items');
    }
};
