<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('standard_code')->unique();
            $table->string('standard_name');
            $table->text('description')->nullable();
            
            // Technical Specifications
            $table->json('specifications')->nullable(); // {min, max, unit, tolerance}
            $table->json('chemical_properties')->nullable();
            $table->json('physical_properties')->nullable();
            
            // Testing Parameters
            $table->string('test_method')->nullable();
            $table->integer('sample_size')->default(1);
            $table->enum('test_frequency', ['per_batch', 'daily', 'weekly', 'monthly'])->default('per_batch');
            
            // Acceptance Criteria
            $table->decimal('acceptance_threshold', 5, 2)->default(95.00); // %
            $table->integer('max_defects_allowed')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['item_id', 'branch_id']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_standards');
    }
};

