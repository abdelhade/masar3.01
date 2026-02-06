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
            // Handle the project_id foreign key and index only if needed
            // Check if we need to recreate the index (only if column doesn't exist yet)
            if (!Schema::hasColumn('subprojects', 'project_template_id')) {
                try {
                    // Try to drop foreign key constraint first (it uses the index)
                    $table->dropForeign(['project_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or already dropped
                }
                
                try {
                    // Drop the index
                    $table->dropIndex(['project_id', 'name']);
                } catch (\Exception $e) {
                    // Index might not exist
                }
                
                // Recreate the index
                try {
                    $table->index(['project_id', 'name']);
                } catch (\Exception $e) {
                    // Index might already exist
                }
                
                // Recreate the foreign key constraint
                try {
                    $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
            
            // Add new column only if it doesn't exist
            if (!Schema::hasColumn('subprojects', 'project_template_id')) {
                $table->foreignId('project_template_id')->nullable()->after('project_id')->constrained('project_templates')->onDelete('cascade');
            }
            
            // Add index only if it doesn't exist (check by trying to add it)
            try {
                $table->index(['project_template_id', 'name']);
            } catch (\Exception $e) {
                // Index might already exist, which is fine
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subprojects', function (Blueprint $table) {
            $table->dropForeign(['project_template_id']);
            $table->dropIndex(['project_template_id', 'name']);
            $table->dropColumn('project_template_id');
            // Restore original index if needed (it should already exist)
            // The foreign key on project_id should already be restored
        });
    }
};

