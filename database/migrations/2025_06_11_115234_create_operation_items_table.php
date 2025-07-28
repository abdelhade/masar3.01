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
        Schema::create('operation_items', function (Blueprint $table) {
            $table->id();
            $table->integer('pro_tybe')->nullable();
            $table->integer('detail_store')->nullable();
            $table->integer('pro_id')->nullable();
            $table->integer('item_id')->default(0);
            $table->integer('unit_id')->nullable();
            $table->decimal('unit_value', 10, 3)->default(1.000);
            $table->decimal('qty_in', 10, 2)->default(0.00);
            $table->decimal('qty_out', 10, 2)->default(0.00);
            $table->decimal('fat_quantity', 10, 2)->nullable();
            $table->decimal('fat_price', 10, 2)->nullable();
            $table->decimal('item_price', 10, 2)->default(0.00);
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('current_stock_value', 12, 2)->default(0.00);
            $table->decimal('item_discount', 10, 2)->default(0.00);
            $table->decimal('additional', 10, 2)->default(0.00);
            $table->decimal('detail_value', 10, 2)->default(0.00);
            $table->decimal('profit', 10, 2)->default(0.00);
            $table->string('notes')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_numbers')->nullable();
            $table->boolean('is_stock')->default(false);
            $table->boolean('isdeleted')->default(0);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('currency_rate', 15, 6)->default(0);
            $table->integer('tenant')->default(0);
            $table->integer('branch')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_items');
    }
};