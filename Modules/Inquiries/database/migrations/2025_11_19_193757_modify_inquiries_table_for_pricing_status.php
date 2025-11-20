<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_status_id')->nullable()->after('total_project_value');
            $table->foreign('pricing_status_id')->references('id')->on('pricing_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {

            if (Schema::hasColumn('inquiries', 'pricing_status_id')) {
                $table->dropForeign(['pricing_status_id']);
            }

            $table->dropColumn('pricing_status_id');

            $table->enum('quotation_state', ['pending', 'won', 'lost', 'no_quote'])
                ->nullable()
                ->after('total_project_value');
        });
    }
};
