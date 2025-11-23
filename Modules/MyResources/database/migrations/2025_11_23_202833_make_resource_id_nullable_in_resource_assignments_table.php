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
        Schema::table('resource_assignments', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['resource_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('resource_id')->nullable()->change();
            
            // Re-add foreign key with nullOnDelete
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resource_assignments', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['resource_id']);
            
            // Make column required again
            $table->unsignedBigInteger('resource_id')->nullable(false)->change();
            
            // Re-add foreign key with cascadeOnDelete
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->cascadeOnDelete();
        });
    }
};
