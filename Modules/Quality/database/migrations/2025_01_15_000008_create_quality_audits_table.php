<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            
            // Audit Info
            $table->string('audit_title');
            $table->enum('audit_type', [
                'internal',
                'external',
                'supplier',
                'certification',
                'customer'
            ]);
            $table->enum('audit_scope', [
                'full_system',
                'process_specific',
                'product_specific',
                'department_specific'
            ])->default('full_system');
            
            // Schedule
            $table->date('planned_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // Auditor
            $table->foreignId('lead_auditor_id')->constrained('users');
            $table->json('audit_team')->nullable(); // Array of user IDs
            $table->string('external_auditor')->nullable();
            $table->string('external_organization')->nullable();
            
            // Scope Details
            $table->text('audit_objectives')->nullable();
            $table->json('areas_covered')->nullable();
            $table->json('standards_referenced')->nullable();
            
            // Checklist
            $table->json('checklist')->nullable(); // Dynamic checklist items
            
            // Findings
            $table->integer('total_findings')->default(0);
            $table->integer('critical_findings')->default(0);
            $table->integer('major_findings')->default(0);
            $table->integer('minor_findings')->default(0);
            $table->integer('observations')->default(0);
            
            // Results
            $table->enum('overall_result', [
                'pass',
                'pass_with_observations',
                'conditional',
                'fail'
            ])->nullable();
            
            $table->text('summary')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('recommendations')->nullable();
            
            // Follow-up
            $table->date('follow_up_date')->nullable();
            $table->enum('follow_up_status', ['pending', 'in_progress', 'completed'])->default('pending');
            
            // Status
            $table->enum('status', [
                'planned',
                'in_progress',
                'draft_report',
                'review_pending',
                'completed',
                'cancelled'
            ])->default('planned');
            
            // Attachments
            $table->json('attachments')->nullable(); // Reports, evidence photos
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['planned_date', 'status']);
            $table->index('audit_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_audits');
    }
};

