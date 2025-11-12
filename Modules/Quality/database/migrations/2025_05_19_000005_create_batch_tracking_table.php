<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            
            // Batch Info
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->decimal('remaining_quantity', 15, 3);
            
            // Source
            $table->foreignId('supplier_id')->nullable()->constrained('acc_head');
            $table->foreignId('purchase_invoice_id')->nullable()->constrained('operhead');
            $table->foreignId('manufacturing_order_id')->nullable()->constrained('operhead');
            
            // Quality Info
            $table->foreignId('inspection_id')->nullable()->constrained('quality_inspections');
            $table->enum('quality_status', ['passed', 'failed', 'conditional', 'quarantine'])->default('passed');
            $table->text('quality_notes')->nullable();
            
            // Storage
            $table->foreignId('warehouse_id')->nullable()->constrained('acc_head');
            $table->string('location')->nullable();
            
            // Traceability
            $table->json('parent_batches')->nullable(); // للمنتجات المصنعة من عدة مواد
            $table->json('child_batches')->nullable(); // الدفعات المشتقة من هذه الدفعة
            
            // Status
            $table->enum('status', ['active', 'depleted', 'expired', 'quarantine', 'scrapped'])->default('active');
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['item_id', 'status']);
            $table->index('production_date');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_tracking');
    }
};

