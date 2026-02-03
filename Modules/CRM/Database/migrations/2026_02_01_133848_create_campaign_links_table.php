<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->text('original_url');
            $table->string('tracking_code')->unique();
            $table->integer('clicks_count')->default(0);
            $table->timestamps();

            $table->index('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_links');
    }
};
