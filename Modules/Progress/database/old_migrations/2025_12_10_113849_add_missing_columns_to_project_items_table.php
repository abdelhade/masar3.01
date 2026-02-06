<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            if (!Schema::hasColumn('project_items', 'item_label')) {
                $table->string('item_label')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('project_items', 'daily_quantity')) {
                $table->decimal('daily_quantity', 10, 2)->nullable()->after('item_label');
            }
            if (!Schema::hasColumn('project_items', 'shift')) {
                $table->string('shift')->nullable()->after('daily_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            $table->dropColumn(['item_label', 'daily_quantity', 'shift']);
        });
    }
};
