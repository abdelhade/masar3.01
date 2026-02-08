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
        Schema::table('issues', function (Blueprint $table) {
            $table->date('deadline')->nullable();
        });

        // Copy data from due_date to deadline column
        $issues = Issue::whereNotNull('due_date')->get();
        foreach ($issues as $issue) {
            $issue->update(['deadline' => $issue->due_date]);
        }

        // Optionally, drop the due_date column after copying if desired
        // Schema::table('issues', function (Blueprint $table) {
        //     $table->dropColumn('due_date');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('deadline');
        });
    }
};
