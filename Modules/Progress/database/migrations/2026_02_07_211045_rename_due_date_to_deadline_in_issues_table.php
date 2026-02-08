<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Progress\Models\Issue;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the deadline column
        Schema::table('issues', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('assigned_to');
        });
        
        // Copy data from due_date to deadline
        $issues = Issue::whereNotNull('due_date')->get();
        foreach ($issues as $issue) {
            $issue->update(['deadline' => $issue->due_date]);
        }
        
        // Drop the due_date column
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the due_date column
        Schema::table('issues', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('assigned_to');
        });
        
        // Copy data from deadline to due_date
        $issues = Issue::whereNotNull('deadline')->get();
        foreach ($issues as $issue) {
            $issue->update(['due_date' => $issue->deadline]);
        }
        
        // Drop the deadline column
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('deadline');
        });
    }
};
