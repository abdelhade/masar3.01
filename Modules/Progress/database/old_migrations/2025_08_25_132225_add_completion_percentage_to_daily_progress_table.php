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
    Schema::table('daily_progress', function (Blueprint $table) {
        $table->decimal('completion_percentage', 5, 2)->default(0)->after('employee_id');
    });
}

public function down()
{
    Schema::table('daily_progress', function (Blueprint $table) {
        $table->dropColumn('completion_percentage');
    });
}

};
