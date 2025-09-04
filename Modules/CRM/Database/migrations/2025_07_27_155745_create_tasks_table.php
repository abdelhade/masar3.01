<?php

use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade'); // اسم العميل
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // المستخدم (المندوب)
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete(); // العميل المستهدف (لو مستخدم داخلي)

            // بدل work_item_type_id
            $table->foreignId('task_type_id')->constrained('task_types')->onDelete('cascade');

            // الحقول النصية
            $table->string('title'); // عنوان التاسك

            // Enums
            $table->enum('priority', array_column(TaskPriorityEnum::cases(), 'value'));
            $table->enum('status', array_column(TaskStatusEnum::cases(), 'value'))->default(TaskStatusEnum::PENDING->value);

            // باقي التفاصيل
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable(); // تاريخ التسليم
            $table->text('client_comment')->nullable(); // تعليق العميل
            $table->text('user_comment')->nullable(); // تعليق المستخدم

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
