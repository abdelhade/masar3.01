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
        Schema::table('resource_types', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['resource_category_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('resource_category_id')->nullable()->change();
            
            // Re-add foreign key with nullOnDelete
            $table->foreign('resource_category_id')
                ->references('id')
                ->on('resource_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['resource_category_id']);
            
            // Make column required again
            $table->unsignedBigInteger('resource_category_id')->nullable(false)->change();
            
            // Re-add foreign key with cascadeOnDelete
            $table->foreign('resource_category_id')
                ->references('id')
                ->on('resource_categories')
                ->cascadeOnDelete();
        });
    }
};
