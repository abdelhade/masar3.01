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
        Schema::table('acc_head', function (Blueprint $table) {
            // إضافة العمود مع جعله قابلًا للقبول NULL
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete()
                ->comment('___');
            $table->foreignId('rent_to')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete()
                ->comment('___');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_head', function (Blueprint $table) {
            //
        });
    }
};
