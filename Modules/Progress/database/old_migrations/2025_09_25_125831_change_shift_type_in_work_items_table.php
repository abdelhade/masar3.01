<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_items', function (Blueprint $table) {
            // يحول عمود shift من string إلى integer
            $table->integer('shift')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_items', function (Blueprint $table) {
            // يرجعه لنوعه القديم
            $table->string('shift')->nullable()->change();
        });
    }
};
