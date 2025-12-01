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
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('director_id')->nullable();
            $table->unsignedBigInteger('deputy_director_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('director_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('deputy_director_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['departments_parent_id_foreign']);
            $table->dropForeign(['departments_director_id_foreign']);
            $table->dropForeign(['departments_deputy_director_id_foreign']);
            $table->dropColumn(['parent_id', 'director_id', 'deputy_director_id']);
        });
    }
};
