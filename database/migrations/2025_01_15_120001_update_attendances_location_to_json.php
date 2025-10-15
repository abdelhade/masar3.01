<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the table exists and has the location column
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'location')) {
            Schema::table('attendances', function (Blueprint $table) {
                // حذف العمود القديم وإعادة إنشاؤه كـ JSON
                $table->dropColumn('location');
            });
            
            Schema::table('attendances', function (Blueprint $table) {
                // إضافة العمود الجديد كـ JSON
                $table->json('location')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the table exists and has the location column
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'location')) {
            Schema::table('attendances', function (Blueprint $table) {
                // إرجاع حقل location إلى string
                $table->string('location')->nullable()->change();
            });
        }
    }
};
