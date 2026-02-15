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
<<<<<<< HEAD:Modules/POS/database/migrations/2026_02_15_230517_create_kitchen_printer_stations_table.php
        Schema::create('kitchen_printer_stations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
=======
        if (!Schema::hasColumn('issues', 'module')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->string('module')->nullable()->after('assigned_to');
            });
        }
>>>>>>> 91245a54c1948306a1bf54f903f9cec32a3a2f0e:Modules/Progress/database/migrations/2026_02_07_212200_add_module_column_to_issues_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD:Modules/POS/database/migrations/2026_02_15_230517_create_kitchen_printer_stations_table.php
        Schema::dropIfExists('kitchen_printer_stations');
=======
        if (Schema::hasColumn('issues', 'module')) {
            Schema::table('issues', function (Blueprint $table) {
                $table->dropColumn('module');
            });
        }
>>>>>>> 91245a54c1948306a1bf54f903f9cec32a3a2f0e:Modules/Progress/database/migrations/2026_02_07_212200_add_module_column_to_issues_table.php
    }
};
