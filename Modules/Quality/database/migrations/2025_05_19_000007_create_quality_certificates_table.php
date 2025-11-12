<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            
            // Certificate Details
            $table->string('certificate_name');
            $table->enum('certificate_type', [
                'ISO_9001',
                'ISO_22000',
                'HACCP',
                'GMP',
                'HALAL',
                'FDA',
                'CE',
                'custom'
            ]);
            $table->string('custom_type')->nullable();
            
            // Issuing Authority
            $table->string('issuing_authority');
            $table->string('authority_contact')->nullable();
            
            // Dates
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->date('last_audit_date')->nullable();
            $table->date('next_audit_date')->nullable();
            
            // Scope
            $table->text('scope')->nullable();
            $table->json('covered_items')->nullable(); // أصناف مشمولة
            $table->json('covered_processes')->nullable(); // عمليات مشمولة
            
            // Status
            $table->enum('status', ['active', 'expired', 'suspended', 'cancelled', 'renewal_pending'])->default('active');
            
            // Notifications
            $table->boolean('notify_before_expiry')->default(true);
            $table->integer('notification_days')->default(90); // قبل الانتهاء بـ 90 يوم
            
            // Attachments
            $table->json('attachments')->nullable(); // PDF certificates, audit reports
            
            // Cost
            $table->decimal('certificate_cost', 15, 2)->default(0);
            $table->decimal('renewal_cost', 15, 2)->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'expiry_date']);
            $table->index('certificate_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_certificates');
    }
};

