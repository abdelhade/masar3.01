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
        Schema::create('cashier_transactions', function (Blueprint $table) {
            $table->id();
            
            // IDs للمزامنة
            $table->string('local_id', 36)->nullable()->unique()->comment('UUID من IndexedDB');
            $table->unsignedBigInteger('server_id')->nullable()->unique()->comment('ID من operhead بعد المزامنة');
            
            // بيانات المعاملة
            $table->unsignedBigInteger('pro_type_id')->default(102)->comment('نوع الفاتورة (فاتورة كاشير)');
            $table->unsignedInteger('pro_id')->nullable()->comment('رقم الفاتورة');
            $table->date('pro_date')->nullable();
            $table->date('accural_date')->nullable();
            $table->string('pro_serial', 50)->nullable();
            $table->string('pro_num', 50)->nullable();
            
            // الحسابات
            $table->foreignId('customer_id')->nullable()->constrained('acc_head')->nullOnDelete()->comment('العميل');
            $table->foreignId('store_id')->nullable()->constrained('acc_head')->nullOnDelete()->comment('المخزن');
            $table->foreignId('cash_account_id')->nullable()->constrained('acc_head')->nullOnDelete()->comment('حساب الصندوق');
            $table->foreignId('employee_id')->nullable()->constrained('acc_head')->nullOnDelete()->comment('الموظف');
            
            // المبالغ المالية
            $table->decimal('subtotal', 15, 2)->default(0)->comment('الإجمالي');
            $table->decimal('discount', 15, 2)->default(0)->comment('الخصم');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('نسبة الخصم');
            $table->decimal('additional', 15, 2)->default(0)->comment('الإضافي');
            $table->decimal('additional_percentage', 5, 2)->default(0)->comment('نسبة الإضافي');
            $table->decimal('total', 15, 2)->default(0)->comment('الصافي');
            
            // الدفع
            $table->string('payment_method', 50)->nullable()->comment('طريقة الدفع');
            $table->decimal('cash_amount', 15, 2)->default(0)->comment('المبلغ نقداً');
            $table->decimal('card_amount', 15, 2)->default(0)->comment('المبلغ بالبطاقة');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('المبلغ المدفوع');
            
            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->unsignedInteger('table_id')->nullable()->comment('رقم الطاولة');
            
            // حالة المزامنة
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending')->comment('حالة المزامنة');
            $table->timestamp('synced_at')->nullable()->comment('تاريخ المزامنة');
            $table->text('sync_error')->nullable()->comment('خطأ المزامنة');
            
            // بيانات الأصناف (JSON)
            $table->json('items')->nullable()->comment('تفاصيل الأصناف');
            
            // معلومات المستخدم والفرع
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->default(1)->constrained('branches')->nullOnDelete();
            $table->tinyInteger('isdeleted')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('local_id');
            $table->index('server_id');
            $table->index('sync_status');
            $table->index('pro_date');
            $table->index('customer_id');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_transactions');
    }
};
