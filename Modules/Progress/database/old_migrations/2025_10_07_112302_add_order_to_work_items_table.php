<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('work_items', function (Blueprint $table) {
            // إضافة العمود فقط لو مش موجود
            if (!Schema::hasColumn('work_items', 'order')) {
                $table->integer('order')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_items', function (Blueprint $table) {
            // حذف العمود لو موجود
            if (Schema::hasColumn('work_items', 'order')) {
                $table->dropColumn('order');
            }
        });
    }
};
