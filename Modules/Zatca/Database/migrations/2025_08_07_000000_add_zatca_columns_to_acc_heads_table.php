<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('acc_head', function (Blueprint $table) {
            $table->string('zatca_name')->nullable(); // الاسم التجاري
            $table->string('vat_number')->nullable(); //الرقم الضريبي
            $table->string('national_id')->nullable(); // رقم الهوية
            $table->string('zatca_address')->nullable(); //العنوان الوطني
            $table->string('company_type')->nullable(); //نوع العميل (شركة - فردي)
            $table->string('nationality')->nullable(); //الجنسية
        });
    }

    public function down()
    {
        Schema::table('acc_head', function (Blueprint $table) {
            $table->dropColumn([
                'zatca_name',
                'vat_number',
                'national_id',
                'zatca_address',
                'company_type',
                'nationality',
            ]);
        });
    }
};