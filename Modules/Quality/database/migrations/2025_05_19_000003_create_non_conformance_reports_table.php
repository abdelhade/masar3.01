<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_conformance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ncr_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            
            // Related to
            $table->foreignId('inspection_id')->nullable()->constrained('quality_inspections');
            $table->foreignId('item_id')->constrained('items');
            $table->string('batch_number')->nullable();
            $table->decimal('affected_quantity', 15, 3);
            
            // NCR Details
            $table->enum('source', [
                'receiving_inspection',
                'in_process',
                'final_inspection',
                'customer_complaint',
                'internal_audit',
                'supplier_notification'
            ]);
            
            $table->dateTime('detected_date');
            $table->foreignId('detected_by')->constrained('users');
            
            // Problem Description
            $table->text('problem_description');
            $table->text('root_cause')->nullable();
            $table->enum('severity', ['critical', 'major', 'minor'])->default('minor');
            
            // Cost Impact
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            
            // Immediate Action
            $table->text('immediate_action')->nullable();
            $table->enum('disposition', [
                'rework',
                'scrap',
                'return_to_supplier',
                'use_as_is',
                'repair',
                'downgrade'
            ])->nullable();
            
            // Responsible
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->date('target_closure_date')->nullable();
            $table->date('actual_closure_date')->nullable();
            
            // Status
            $table->enum('status', [
                'open',
                'investigating',
                'action_plan_pending',
                'implementing',
                'verification_pending',
                'closed',
                'cancelled'
            ])->default('open');
            
            // Attachments
            $table->json('attachments')->nullable();
            
            // Closure
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->text('closure_notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['detected_date', 'branch_id']);
            $table->index(['status', 'severity']);
            $table->index('batch_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_conformance_reports');
    }
};

