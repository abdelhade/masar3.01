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
        // Check if account_id column exists before renaming
        if (Schema::hasColumn('acc_head', 'account_id') && ! Schema::hasColumn('acc_head', 'accountable_id')) {
            Schema::table('acc_head', function (Blueprint $table) {
                $table->renameColumn('account_id', 'accountable_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if accountable_id column exists before renaming back
        if (Schema::hasColumn('acc_head', 'accountable_id') && ! Schema::hasColumn('acc_head', 'account_id')) {
            Schema::table('acc_head', function (Blueprint $table) {
                $table->renameColumn('accountable_id', 'account_id');
            });
        }
    }
};
