<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->enum('status', ['pending', 'sent', 'failed', 'opened', 'clicked'])->default('pending');
            $table->string('email');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->string('tracking_code')->unique()->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_logs');
    }
};
