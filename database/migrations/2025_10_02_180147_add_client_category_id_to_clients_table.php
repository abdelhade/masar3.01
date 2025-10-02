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
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('client_category_id')
                ->nullable()
                ->constrained('client_categories')
                ->nullOnDelete();

            $table->boolean('is_active')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['client_category_id']);
            $table->dropColumn('client_category_id');

            $table->boolean('is_active')->default(null)->change();
        });
    }
};
