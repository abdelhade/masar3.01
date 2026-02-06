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
        $table->integer('expected_quantity_per_day')->nullable()->after('unit');
    });
}

public function down()
{
    Schema::table('work_items', function (Blueprint $table) {
        $table->dropColumn('expected_quantity_per_day');
    });
}

};
