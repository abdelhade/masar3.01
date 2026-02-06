<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectsTableChangeHolidaysColumn extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // تعديل الأعمدة وإزالة الـ default
            $table->tinyInteger('working_days')->nullable(false)->default(null)->change();
            $table->tinyInteger('daily_work_hours')->nullable(false)->default(null)->change();

            // نحذف holidays ونضيف weekly_holidays مكانه
            $table->dropColumn('holidays');
            $table->string('weekly_holidays')->nullable()->after('daily_work_hours');
            // أو لو عايزة تخزنيها كمصفوفة:
            // $table->json('weekly_holidays')->nullable()->after('daily_work_hours');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->tinyInteger('working_days')->default(5)->change();
            $table->tinyInteger('daily_work_hours')->default(8)->change();

            $table->dropColumn('weekly_holidays');
            $table->smallInteger('holidays')->default(0);
        });
    }
}
