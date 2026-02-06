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
        $table->softDeletes(); // ده بيضيف عمود deleted_at nullable
    });
}

public function down()
{
    Schema::table('work_items', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
}

};
