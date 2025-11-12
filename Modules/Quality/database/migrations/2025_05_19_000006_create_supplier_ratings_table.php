<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('acc_head')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            
            // Rating Period
            $table->date('rating_date');
            $table->enum('period_type', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->date('period_start');
            $table->date('period_end');
            
            // Quality Metrics
            $table->decimal('quality_score', 5, 2)->default(0); // 0-100
            $table->integer('total_inspections')->default(0);
            $table->integer('passed_inspections')->default(0);
            $table->integer('failed_inspections')->default(0);
            $table->decimal('pass_rate', 5, 2)->default(0); // %
            
            // Delivery Metrics
            $table->decimal('delivery_score', 5, 2)->default(0); // 0-100
            $table->integer('total_deliveries')->default(0);
            $table->integer('on_time_deliveries')->default(0);
            $table->decimal('on_time_rate', 5, 2)->default(0); // %
            
            // Documentation Metrics
            $table->decimal('documentation_score', 5, 2)->default(0); // 0-100
            $table->integer('certificates_required')->default(0);
            $table->integer('certificates_received')->default(0);
            
            // NCR Metrics
            $table->integer('ncrs_raised')->default(0);
            $table->integer('critical_ncrs')->default(0);
            $table->integer('major_ncrs')->default(0);
            $table->integer('minor_ncrs')->default(0);
            
            // Overall Rating
            $table->decimal('overall_score', 5, 2)->default(0); // 0-100
            $table->enum('rating', ['excellent', 'good', 'acceptable', 'poor', 'unacceptable'])->default('acceptable');
            
            // Comments & Actions
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('improvement_required')->nullable();
            $table->text('recommended_actions')->nullable();
            
            // Status
            $table->enum('supplier_status', ['approved', 'conditional', 'suspended', 'blacklisted'])->default('approved');
            
            // Audit
            $table->foreignId('rated_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['supplier_id', 'rating_date']);
            $table->index('rating');
            $table->unique(['supplier_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_ratings');
    }
};

