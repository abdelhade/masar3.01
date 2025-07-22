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
         Schema::table('operhead', function (Blueprint $table) {
            // إضافة العمود مع جعله قابلًا للقبول NULL
            $table->foreignId('acc3')
                  ->nullable()
                  ->constrained('acc_head')
                  ->nullOnDelete()
                  ->comment('___');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operhead', function (Blueprint $table) {
            //
        });
    }
};
