<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            
            // What is being inspected
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('quality_standard_id')->nullable()->constrained('quality_standards');
            $table->string('batch_number')->nullable();
            $table->decimal('quantity_inspected', 15, 3);
            
            // Related Documents
            $table->foreignId('purchase_invoice_id')->nullable()->constrained('operhead');
            $table->foreignId('manufacturing_order_id')->nullable()->constrained('operhead');
            $table->foreignId('supplier_id')->nullable()->constrained('acc_head');
            
            // Inspection Details
            $table->enum('inspection_type', [
                'receiving', // استلام مواد خام
                'in_process', // أثناء الإنتاج
                'final', // فحص نهائي
                'random', // فحص عشوائي
                'customer_complaint' // شكوى عميل
            ]);
            $table->dateTime('inspection_date');
            $table->foreignId('inspector_id')->constrained('users');
            
            // Results
            $table->json('test_results')->nullable(); // Array of test results
            $table->decimal('pass_quantity', 15, 3)->default(0);
            $table->decimal('fail_quantity', 15, 3)->default(0);
            $table->decimal('pass_percentage', 5, 2)->default(0);
            
            $table->enum('result', ['pass', 'fail', 'conditional'])->default('pass');
            $table->text('defects_found')->nullable();
            $table->text('inspector_notes')->nullable();
            
            // Actions Taken
            $table->enum('action_taken', [
                'accepted',
                'rejected', 
                'rework',
                'conditional_accept',
                'pending_review'
            ])->default('pending_review');
            
            // Attachments
            $table->json('attachments')->nullable(); // Photos, certificates, reports
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['inspection_date', 'branch_id']);
            $table->index(['item_id', 'result']);
            $table->index('batch_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
};

