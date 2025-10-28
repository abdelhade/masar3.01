<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_items', function (Blueprint $table) {
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('density', 10, 2)->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('operation_items', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height', 'density']);
        });
    }
};
