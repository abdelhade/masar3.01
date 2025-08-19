<?php

use Illuminate\Support\Facades\DB;
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
        Schema::create('barcode_print_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم الإعداد
            $table->string('company_name')->default('اسم الشركة'); // اسم الشركة
            $table->string('paper_width')->default('25'); // عرض الورقة (mm)
            $table->string('paper_height')->default('38'); // ارتفاع الورقة (mm)
            $table->string('margin_top')->default('2'); // هامش علوي (mm)
            $table->string('margin_bottom')->default('2'); // هامش سفلي (mm)
            $table->string('margin_left')->default('2'); // هامش يساري (mm)
            $table->string('margin_right')->default('2'); // هامش يميني (mm)
            $table->boolean('show_company_name')->default(true); // عرض اسم الشركة
            $table->boolean('show_item_name')->default(true); // عرض اسم الصنف
            $table->boolean('show_item_code')->default(true); // عرض كود الصنف
            $table->boolean('show_barcode_image')->default(true); // عرض صورة الباركود
            $table->boolean('show_price_before_discount')->default(false); // عرض السعر قبل الخصم
            $table->boolean('show_price_after_discount')->default(true); // عرض السعر بعد الخصم
            $table->string('font_size_company')->default('10'); // حجم خط اسم الشركة (pt)
            $table->string('font_size_item')->default('8'); // حجم خط اسم الصنف (pt)
            $table->string('font_size_price')->default('9'); // حجم خط السعر (pt)
            $table->string('barcode_width')->default('50'); // عرض الباركود (mm)
            $table->string('barcode_height')->default('15'); // ارتفاع الباركود (mm)
            $table->boolean('invert_colors')->default(false); // عكس الألوان (أبيض على أسود)
            $table->string('text_align')->default('center'); // محاذاة النص (center, left, right)
            $table->boolean('is_default')->default(false); // الإعداد الافتراضي
            $table->boolean('is_active')->default(true); // نشط/غير نشط
            $table->json('custom_fields')->nullable(); // حقول إضافية مخصصة
            $table->timestamps();
        });

        DB::table('barcode_print_settings')->insert([
            'name' => 'إعداد افتراضي',
            'company_name' => 'اسم شركتك',
            'paper_width' => '25',
            'paper_height' => '38',
            'margin_top' => '2',
            'margin_bottom' => '2',
            'margin_left' => '2',
            'margin_right' => '2',
            'show_company_name' => true,
            'show_item_name' => true,
            'show_item_code' => true,
            'show_barcode_image' => true,
            'show_price_before_discount' => false,
            'show_price_after_discount' => true,
            'font_size_company' => '10',
            'font_size_item' => '8',
            'font_size_price' => '9',
            'barcode_width' => '50',
            'barcode_height' => '15',
            'invert_colors' => false,
            'text_align' => 'center',
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_print_settings');
    }
};
