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
        Schema::table('acc_head', function (Blueprint $table) {
            $table->string('accountable_type')->nullable();
            $table->unsignedBigInteger('accountable_id')->nullable();
            $table->index(['accountable_type', 'accountable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_head', function (Blueprint $table) {
            $table->dropColumn('accountable_type');
            $table->dropColumn('accountable_id');
            $table->dropIndex(['accountable_type', 'accountable_id']);
        });
    }
};
