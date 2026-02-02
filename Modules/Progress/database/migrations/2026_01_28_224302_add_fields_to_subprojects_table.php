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
            $table->date('start_date')->nullable()->after('name');
            $table->date('end_date')->nullable()->after('start_date');
            $table->decimal('total_quantity', 12, 2)->nullable()->after('end_date');
            $table->text('description')->nullable()->after('total_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subprojects', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'total_quantity', 'description']);
        });
    }
};
