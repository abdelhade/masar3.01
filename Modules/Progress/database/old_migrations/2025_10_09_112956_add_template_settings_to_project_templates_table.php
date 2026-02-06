<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_templates', function (Blueprint $table) {
            $table->string('status')->default('active')->after('description');
            $table->foreignId('project_type_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->integer('working_days')->default(5)->after('project_type_id');
            $table->integer('daily_work_hours')->default(8)->after('working_days');
            $table->string('weekly_holidays')->default('5,6')->after('daily_work_hours');
            $table->string('working_zone')->nullable()->after('weekly_holidays');
        });

        Schema::table('template_items', function (Blueprint $table) {
            $table->decimal('estimated_daily_qty', 10, 2)->default(0)->after('default_quantity');
            $table->integer('duration')->default(0)->after('estimated_daily_qty');
            $table->string('predecessor')->nullable()->after('duration');
            $table->string('dependency_type')->default('end_to_start')->after('predecessor');
            $table->integer('lag')->default(0)->after('dependency_type');
            $table->text('notes')->nullable()->after('lag');
        });
    }

    public function down()
    {
        Schema::table('project_templates', function (Blueprint $table) {
            $table->dropColumn(['status', 'project_type_id', 'working_days', 'daily_work_hours', 'weekly_holidays', 'working_zone']);
        });

        Schema::table('template_items', function (Blueprint $table) {
            $table->dropColumn(['estimated_daily_qty', 'duration', 'predecessor', 'dependency_type', 'lag', 'notes']);
        });
    }
};
