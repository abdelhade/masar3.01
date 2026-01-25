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
            if (!Schema::hasColumn('project_items', 'notes')) {
                $table->text('notes')->nullable()->after('subproject_name');
            }
            if (!Schema::hasColumn('project_items', 'is_measurable')) {
                $table->boolean('is_measurable')->default(true)->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            $table->dropColumn(['notes', 'is_measurable']);
        });
    }
};
