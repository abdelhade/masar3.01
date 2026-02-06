<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes to improve query performance on frequently used columns
     */
    public function up(): void
    {
        // Add indexes to work_items table
        if (Schema::hasTable('work_items')) {
            Schema::table('work_items', function (Blueprint $table) {
                try {
                    $table->index('category_id', 'work_items_category_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('order', 'work_items_order_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
            });
        }

        // Add indexes to project_items table
        if (Schema::hasTable('project_items')) {
            Schema::table('project_items', function (Blueprint $table) {
                try {
                    $table->index('project_id', 'project_items_project_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('work_item_id', 'project_items_work_item_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('item_order', 'project_items_item_order_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
            });
        }

        // Add indexes to template_items table
        if (Schema::hasTable('template_items')) {
            Schema::table('template_items', function (Blueprint $table) {
                try {
                    $table->index('project_template_id', 'template_items_project_template_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('work_item_id', 'template_items_work_item_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('item_order', 'template_items_item_order_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
            });
        }

        // Add indexes to project_templates table
        if (Schema::hasTable('project_templates')) {
            Schema::table('project_templates', function (Blueprint $table) {
                try {
                    $table->index('status', 'project_templates_status_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('project_type_id', 'project_templates_project_type_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
            });
        }

        // Add indexes to projects table
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                try {
                    $table->index('status', 'projects_status_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('is_draft', 'projects_is_draft_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
                try {
                    $table->index('client_id', 'projects_client_id_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
            });
        }

        // Add indexes to work_item_categories table
        if (Schema::hasTable('work_item_categories')) {
            Schema::table('work_item_categories', function (Blueprint $table) {
                try {
                    $table->index('name', 'work_item_categories_name_index');
                } catch (\Exception $e) {
                    // Index already exists
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from work_items table
        if (Schema::hasTable('work_items')) {
            Schema::table('work_items', function (Blueprint $table) {
                try {
                    $table->dropIndex('work_items_category_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('work_items_order_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
            });
        }

        // Drop indexes from project_items table
        if (Schema::hasTable('project_items')) {
            Schema::table('project_items', function (Blueprint $table) {
                try {
                    $table->dropIndex('project_items_project_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('project_items_work_item_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('project_items_item_order_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
            });
        }

        // Drop indexes from template_items table
        if (Schema::hasTable('template_items')) {
            Schema::table('template_items', function (Blueprint $table) {
                try {
                    $table->dropIndex('template_items_project_template_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('template_items_work_item_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('template_items_item_order_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
            });
        }

        // Drop indexes from project_templates table
        if (Schema::hasTable('project_templates')) {
            Schema::table('project_templates', function (Blueprint $table) {
                try {
                    $table->dropIndex('project_templates_status_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('project_templates_project_type_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
            });
        }

        // Drop indexes from projects table
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                try {
                    $table->dropIndex('projects_status_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('projects_is_draft_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
                try {
                    $table->dropIndex('projects_client_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
            });
        }

        // Drop indexes from work_item_categories table
        if (Schema::hasTable('work_item_categories')) {
            Schema::table('work_item_categories', function (Blueprint $table) {
                try {
                    $table->dropIndex('work_item_categories_name_index');
                } catch (\Exception $e) {
                    // Index doesn't exist
                }
            });
        }
    }
};
