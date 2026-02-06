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
    Schema::table('project_items', function (Blueprint $table) {
        $table->decimal('estimated_daily_qty', 10, 2)->nullable()->after('total_quantity');
    });
}

public function down()
{
    Schema::table('project_items', function (Blueprint $table) {
        $table->dropColumn('estimated_daily_qty');
    });
}

};
