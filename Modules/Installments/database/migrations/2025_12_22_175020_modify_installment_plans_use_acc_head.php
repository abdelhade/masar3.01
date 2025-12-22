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
        Schema::table('installment_plans', function (Blueprint $table) {

            $table->unsignedBigInteger('acc_head_id')->nullable();

            if (Schema::hasColumn('installment_plans', 'client_id')) {
                $table->dropColumn('client_id');
            }

            $table->foreign('acc_head_id')
                ->references('id')
                ->on('acc_head')
                ->onDelete('cascade');

            // Add index for performance
            $table->index('acc_head_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->dropForeign(['acc_head_id']);
            $table->dropIndex(['acc_head_id']);
            $table->dropColumn('acc_head_id');

            $table->foreignId('client_id')
                ->after('id')
                ->comment('يمكنك تغييره لـ user_id أو حسب جدول العملاء لديك');
        });
    }
};
