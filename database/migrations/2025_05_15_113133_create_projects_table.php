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
        // التحقق من وجود الجدول أولاً
        if (Schema::hasTable('projects')) {
            // إضافة الـ foreign key فقط إذا لم يكن موجود
            Schema::table('projects', function (Blueprint $table) {
                // التحقق من وجود العمود
                if (!Schema::hasColumn('projects', 'project_type_id')) {
                    $table->foreignId('project_type_id')->nullable()->after('working_zone');
                }
            });
            
            // إضافة الـ foreign key constraint
            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->foreign('project_type_id')->references('id')->on('project_types')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key already exists, skip
            }
            
            return;
        }
        
        // إنشاء الجدول من الصفر
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('acc_head')->onDelete('set null');
            
            // معلومات الجدولة
            $table->tinyInteger('working_days')->default(5);
            $table->tinyInteger('daily_work_hours')->default(8);
            $table->string('holidays')->nullable(); // comma-separated holidays (e.g. "5,6")
            $table->string('weekly_holidays')->nullable(); // أيام الإجازة الأسبوعية
            
            $table->decimal('budget', 15, 2)->default(0)->nullable();
            $table->string('working_zone')->nullable();
            $table->foreignId('project_type_id')->nullable()->constrained('project_types')->nullOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // الحالة والمسودة
            $table->enum('status', ['draft', 'pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->json('settings')->nullable();
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_progress')->nullable();
            
            // من أنشأ المشروع
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
