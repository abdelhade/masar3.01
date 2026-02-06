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
        Schema::table('subprojects', function (Blueprint $table) {
            if (!Schema::hasColumn('subprojects', 'weight')) {
                $table->decimal('weight', 8, 2)->default(1.00)->after('total_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subprojects', function (Blueprint $table) {
            if (Schema::hasColumn('subprojects', 'weight')) {
                $table->dropColumn('weight');
            }
        });
    }
};
