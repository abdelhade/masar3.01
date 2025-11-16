<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->comment('يمكنك تغييره لـ user_id أو حسب جدول العملاء لديك');
            $table->foreignId('invoice_id')->nullable()->comment('الفاتورة الأصلية إن وجدت');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('amount_to_be_installed', 15, 2);
            $table->integer('number_of_installments');
            $table->date('start_date');
            $table->enum('interval_type', ['monthly', 'daily'])->default('monthly');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installment_plans');
    }
};
