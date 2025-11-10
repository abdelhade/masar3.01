<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->string('capa_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('ncr_id')->constrained('non_conformance_reports')->cascadeOnDelete();
            
            // CAPA Type
            $table->enum('action_type', [
                'corrective', // تصحيحي
                'preventive'  // وقائي
            ])->default('corrective');
            
            // Action Plan
            $table->text('action_description');
            $table->text('root_cause_analysis')->nullable();
            $table->text('preventive_measures')->nullable();
            
            // Responsibility
            $table->foreignId('responsible_person')->constrained('users');
            $table->foreignId('department_id')->nullable()->constrained('branches'); // or departments table
            
            // Timeline
            $table->date('planned_start_date');
            $table->date('planned_completion_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            
            // Implementation
            $table->integer('completion_percentage')->default(0);
            $table->text('implementation_notes')->nullable();
            
            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->date('verification_date')->nullable();
            $table->text('verification_result')->nullable();
            $table->boolean('is_effective')->default(false);
            
            // Status
            $table->enum('status', [
                'planned',
                'in_progress',
                'completed',
                'verified',
                'closed',
                'cancelled'
            ])->default('planned');
            
            // Attachments
            $table->json('attachments')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'branch_id']);
            $table->index('responsible_person');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};

