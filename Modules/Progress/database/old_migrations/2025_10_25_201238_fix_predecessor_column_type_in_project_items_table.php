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
            // Change predecessor column from string to unsignedBigInteger
            $table->unsignedBigInteger('predecessor')->nullable()->change();
            
            // Add foreign key constraint
            $table->foreign('predecessor')
                  ->references('id')
                  ->on('project_items')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['predecessor']);
            
            // Change back to string
            $table->string('predecessor')->nullable()->change();
        });
    }
};
