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
    Schema::table('projects', function (Blueprint $table) {
        // احذف العمود القديم لو موجود
        if (Schema::hasColumn('projects', 'project_type')) {
            $table->dropColumn('project_type');
        }

        // أضف العمود الجديد
        $table->foreignId('project_type_id')
              ->nullable()
              ->constrained('project_types')
              ->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
