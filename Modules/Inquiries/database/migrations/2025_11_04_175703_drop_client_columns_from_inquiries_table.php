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
        Schema::table('inquiries', function (Blueprint $table) {
            // $table->dropForeign(['client_id']);
            // $table->dropForeign(['main_contractor_id']);
            // $table->dropForeign(['consultant_id']);
            // $table->dropForeign(['owner_id']);
            // $table->dropForeign(['assigned_engineer_id']);

            // $table->dropColumn([
            //     'client_id',
            //     'main_contractor_id',
            //     'consultant_id',
            //     'owner_id',
            //     'assigned_engineer_id',
            // ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            // $table->foreignId('main_contractor_id')->nullable()->constrained('clients')->onDelete('set null');
            // $table->foreignId('consultant_id')->nullable()->constrained('clients')->onDelete('set null');
            // $table->foreignId('owner_id')->nullable()->constrained('clients')->onDelete('set null');
            // $table->foreignId('assigned_engineer_id')->nullable()->constrained('clients')->onDelete('set null');
        });
    }
};
