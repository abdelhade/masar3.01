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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('status_id')->nullable()->constrained('lead_statuses')->onDelete('set null');
            $table->foreignId('source_id')->nullable()->constrained('chance_sources')->onDelete('set null');
            $table->decimal('amount', 12, 2)->nullable(); // القيمة المتوقعة للصفقة
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
