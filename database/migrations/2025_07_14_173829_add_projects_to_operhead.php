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
        Schema::table('operhead', function (Blueprint $table) {
            // إضافة العمود مع جعله قابلًا للقبول NULL
            $table->foreignId('project_id')
                  ->nullable()
                  ->constrained('projects')
                  ->nullOnDelete()
                  ->comment('يرتبط بالمشروع المحدد');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            // حذف العلاقة أولاً (مهم لمنع الأخطاء)
            $table->dropForeign(['project_id']);
            
            // ثم حذف العمود
            $table->dropColumn('project_id');
        });
    }
};