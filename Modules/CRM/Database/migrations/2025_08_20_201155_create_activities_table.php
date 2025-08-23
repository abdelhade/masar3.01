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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->text('description')->nullable();
            $table->timestamp('activity_date')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('client_id')->references('id')->on('crm_clients')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
