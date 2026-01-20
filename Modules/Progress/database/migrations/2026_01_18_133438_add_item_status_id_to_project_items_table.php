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
            if (!Schema::hasColumn('project_items', 'item_status_id')) {
                $table->foreignId('item_status_id')->nullable()->constrained('item_statuses')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('project_items', function (Blueprint $table) {
            if (Schema::hasColumn('project_items', 'item_status_id')) {
                $table->dropForeign(['item_status_id']);
                $table->dropColumn('item_status_id');
            }
        });
    }
};
