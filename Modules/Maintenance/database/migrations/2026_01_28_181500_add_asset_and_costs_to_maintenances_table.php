<?php

declare(strict_types=1);

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
        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()
                ->after('branch_id')
                ->constrained('accounts_assets')
                ->nullOnDelete();

            $table->decimal('spare_parts_cost', 15, 2)->default(0)->after('asset_id');
            $table->decimal('labor_cost', 15, 2)->default(0)->after('spare_parts_cost');
            $table->decimal('total_cost', 15, 2)->default(0)->after('labor_cost');

            $table->string('maintenance_type')->nullable()->after('total_cost');
            $table->text('notes')->nullable()->after('maintenance_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropColumn([
                'asset_id',
                'spare_parts_cost',
                'labor_cost',
                'total_cost',
                'maintenance_type',
                'notes',
            ]);
        });
    }
};
