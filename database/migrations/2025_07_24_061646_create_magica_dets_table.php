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
        Schema::create('magica_dets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magical_id')->constrained('magicals')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('option')->nullable();
            $table->string('class')->nullable();
            $table->string('value')->nullable();
            $table->string('placeholder')->nullable();
            $table->boolean('hidden')->default(false);
            $table->boolean('readonly')->default(false);
            
            $table->string('label')->nullable(); // تسمية الحقل
            $table->string('help_text')->nullable(); // نص مساعد
            $table->integer('order')->default(0); // ترتيب العنصر
            $table->boolean('required')->default(false); // هل الحقل مطلوب؟
            $table->json('options')->nullable(); // خيارات إضافية (مثلاً لقائمة منسدلة)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magica_dets');
    }
};
