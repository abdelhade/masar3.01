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
        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('kpi_id')->constrained('kpis')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('weight_percentage');
            $table->timestamps();
            $table->index(['employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpis');
    }
};
