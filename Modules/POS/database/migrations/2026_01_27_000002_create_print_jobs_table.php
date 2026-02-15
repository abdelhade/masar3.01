<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_station_id')
                ->constrained('kitchen_printer_stations')
                ->onDelete('cascade');
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('cashier_transactions')
                ->onDelete('set null');
            $table->text('content');
            $table->enum('status', ['pending', 'success', 'failed', 'retrying'])
                ->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->boolean('is_manual')->default(false);
            $table->foreignId('printed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('printer_station_id');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
