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
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id'); // الخدمة
            $table->unsignedBigInteger('customer_id'); // العميل
            $table->unsignedBigInteger('employee_id')->nullable(); // الموظف المسؤول
            $table->date('booking_date'); // تاريخ الحجز
            $table->decimal('price', 10, 2); // سعر الخدمة
            $table->text('notes')->nullable(); // ملاحظات
            $table->text('customer_notes')->nullable(); // ملاحظات العميل
            $table->boolean('is_completed')->default(false); // مكتمل
            $table->boolean('is_cancelled')->default(false); // ملغي
            $table->timestamp('cancelled_at')->nullable(); // تاريخ الإلغاء
            $table->text('cancellation_reason')->nullable(); // سبب الإلغاء
            $table->unsignedBigInteger('branch_id')->nullable(); // الفرع
            $table->unsignedBigInteger('created_by')->nullable(); // منشئ السجل
            $table->unsignedBigInteger('updated_by')->nullable(); // محدث السجل
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('acc_head')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('acc_head')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['booking_date']);
            $table->index(['service_id', 'booking_date']);
            $table->index(['customer_id', 'booking_date']);
            $table->index(['is_completed', 'is_cancelled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
