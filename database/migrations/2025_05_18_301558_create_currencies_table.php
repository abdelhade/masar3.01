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
            $table->string('name');               // اسم العملة الكامل (مثال: "جنيه مصري")
            $table->string('code', 3)->unique();  // كود العملة (مثل: EGP, USD)
            $table->string('symbol')->nullable(); // رمز العملة (مثل: $, ج.م)
            $table->integer('decimal_places')->default(2); // عدد الكسور العشرية (عادة 2)
            $table->boolean('is_default')->default(false); // لتحديد العملة الافتراضية
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
