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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الخدمة
            $table->string('code')->unique(); // كود الخدمة
            $table->text('description')->nullable(); // وصف الخدمة
            $table->decimal('price', 10, 2)->nullable(); // سعر الخدمة
            $table->decimal('cost', 10, 2)->nullable(); // تكلفة الخدمة
            $table->boolean('is_active')->default(true); // نشط/غير نشط
            $table->boolean('is_taxable')->default(false); // خاضع للضريبة
            $table->foreignId('service_type_id')->nullable()->constrained('service_categories')->onDelete('set null'); // نوع الخدمة
            $table->unsignedBigInteger('branch_id')->nullable(); // الفرع
            $table->foreignId('service_unit_id')->nullable()->constrained('service_units')->onDelete('set null');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            
            $table->index(['is_active', 'branch_id']);
            $table->index(['service_type_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
