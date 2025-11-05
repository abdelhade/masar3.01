<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inquiry_submittal_checklist')) {
            Schema::table('inquiry_submittal_checklist', function (Blueprint $table) {
                if (!Schema::hasColumn('inquiry_submittal_checklist', 'selected_option')) {
                    $table->string('selected_option')->nullable();
                }
            });
        }

        if (Schema::hasTable('inquiry_work_condition')) {
            Schema::table('inquiry_work_condition', function (Blueprint $table) {
                if (!Schema::hasColumn('inquiry_work_condition', 'selected_option')) {
                    $table->string('selected_option')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inquiry_submittal_checklist')) {
            Schema::table('inquiry_submittal_checklist', function (Blueprint $table) {
                if (Schema::hasColumn('inquiry_submittal_checklist', 'selected_option')) {
                    $table->dropColumn('selected_option');
                }
            });
        }

        if (Schema::hasTable('inquiry_work_condition')) {
            Schema::table('inquiry_work_condition', function (Blueprint $table) {
                if (Schema::hasColumn('inquiry_work_condition', 'selected_option')) {
                    $table->dropColumn('selected_option');
                }
            });
        }
    }
};
