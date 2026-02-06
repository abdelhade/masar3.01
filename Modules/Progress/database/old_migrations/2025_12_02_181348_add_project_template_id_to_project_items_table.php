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
            // Drop existing foreign key constraint for project_id
            $table->dropForeign(['project_id']);
        });
        
        // Use DB facade to modify column (Laravel doesn't support ->change() on foreignId directly)
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE project_items MODIFY project_id BIGINT UNSIGNED NULL');
        
        Schema::table('project_items', function (Blueprint $table) {
            // Re-add foreign key constraint (nullable)
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
            
            // Add project_template_id column (nullable)
            $table->foreignId('project_template_id')
                  ->nullable()
                  ->after('project_id')
                  ->constrained('project_templates')
                  ->onDelete('cascade');
            
            // Add index for better performance
            $table->index('project_template_id');
            
            // Add composite index for queries
            $table->index(['project_id', 'project_template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_items', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['project_id', 'project_template_id']);
            $table->dropIndex(['project_template_id']);
            
            // Drop foreign key for project_template_id
            $table->dropForeign(['project_template_id']);
            
            // Remove project_template_id column
            $table->dropColumn('project_template_id');
            
            // Drop foreign key for project_id
            $table->dropForeign(['project_id']);
        });
        
        // Use DB facade to modify column back to NOT NULL
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE project_items MODIFY project_id BIGINT UNSIGNED NOT NULL');
        
        Schema::table('project_items', function (Blueprint $table) {
            // Re-add foreign key constraint
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
        });
    }
};
