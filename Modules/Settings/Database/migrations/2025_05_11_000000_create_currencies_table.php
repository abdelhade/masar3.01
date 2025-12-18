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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();         // (USD, EUR, EGP)
            $table->string('symbol')->nullable(); // رمز العملة ($, €, ج.م)
            $table->integer('decimal_places')->default(2); // عدد الأرقام بعد الفاصلة
            $table->boolean('is_default')->default(false); // العملة الافتراضية للنظام
            $table->boolean('is_active')->default(true);   // تفعيل/إيقاف العملة
            $table->enum('rate_mode', ['automatic', 'manual'])->default('automatic');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
