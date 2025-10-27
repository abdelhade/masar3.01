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
        Schema::create('operation_transitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_operhead_id')->nullable();
            $table->unsignedBigInteger('to_operhead_id')->nullable();
            $table->unsignedTinyInteger('from_state')->nullable();
            $table->unsignedTinyInteger('to_state')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 100)->nullable(); // e.g., 'approve', 'convert_to_quotation', 'convert_to_po', 'receive', 'invoice', 'transfer'
            $table->string('notes', 1000)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedInteger('branch_id')->nullable();

            $table->foreign('from_operhead_id')->references('id')->on('operhead')->onDelete('set null');
            $table->foreign('to_operhead_id')->references('id')->on('operhead')->onDelete('set null');

            $table->index('from_operhead_id');
            $table->index('to_operhead_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_transitions');
    }
};
