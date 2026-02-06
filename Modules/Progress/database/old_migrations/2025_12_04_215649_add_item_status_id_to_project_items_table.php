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
            $table->foreignId('item_status_id')->nullable()->after('is_measurable')->constrained('item_statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            $table->dropForeign(['item_status_id']);
            $table->dropColumn('item_status_id');
        });
    }
};
