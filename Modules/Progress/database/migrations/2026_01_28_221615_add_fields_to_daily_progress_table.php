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
        Schema::table('daily_progress', function (Blueprint $table) {
            $table->decimal('remaining_quantity', 12, 2)->nullable()->after('quantity');
            $table->date('planned_end_date')->nullable()->after('progress_date');
            $table->string('shifts')->nullable()->after('notes');
            $table->string('subproject_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_progress', function (Blueprint $table) {
            $table->dropColumn(['remaining_quantity', 'planned_end_date', 'shifts', 'subproject_name']);
        });
    }
};
