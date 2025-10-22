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
        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_number', 50)->unique();
            $table->string('bank_name', 100);

            $table->string('account_number', 50);
            $table->string('account_holder_name', 100);
            $table->decimal('amount', 15, 2);
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'cleared', 'bounced', 'cancelled'])->default('pending');
            $table->enum('type', ['incoming', 'outgoing'])->default('outgoing');
            $table->string('payee_name', 100)->nullable();
            $table->string('payer_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_number', 50)->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['status', 'type']);
            $table->index(['due_date', 'status']);
            // $table->index('bank_id`');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checks');
    }
};
