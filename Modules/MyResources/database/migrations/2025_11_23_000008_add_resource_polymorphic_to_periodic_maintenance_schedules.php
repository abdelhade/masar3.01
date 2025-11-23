<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodic_maintenance_schedules', function (Blueprint $table) {
            $table->string('maintainable_type')->nullable();
            $table->unsignedBigInteger('maintainable_id')->nullable();
            $table->index(['maintainable_type', 'maintainable_id'], 'pms_maintainable_idx');
        });
    }

    public function down(): void
    {
        Schema::table('periodic_maintenance_schedules', function (Blueprint $table) {
            $table->dropMorphs('maintainable');
        });
    }
};

