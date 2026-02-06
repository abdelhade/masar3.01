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
        // جدول project_types مختلف عن pro_types
        // pro_types = أنواع العمليات (فواتير، سندات، إلخ)
        // project_types = أنواع المشاريع
        
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->integer('tenant')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key للفرع إذا كان موجود
            if (Schema::hasTable('branches')) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_types');
    }
};
