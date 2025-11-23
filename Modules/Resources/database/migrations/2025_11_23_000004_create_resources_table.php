<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            $table->foreignId('resource_category_id')->constrained('resource_categories')->cascadeOnDelete();
            $table->foreignId('resource_type_id')->constrained('resource_types')->cascadeOnDelete();
            $table->foreignId('resource_status_id')->constrained('resource_statuses')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->string('serial_number')->nullable();
            $table->string('model_number')->nullable();
            $table->string('manufacturer')->nullable();
            
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            
            $table->string('current_location')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            
            $table->date('warranty_expiry')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            
            $table->json('specifications')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();

            $table->index(['resource_category_id', 'resource_type_id', 'resource_status_id'], 'res_cat_type_status_idx');
            $table->index('code');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};

