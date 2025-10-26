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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->tinyInteger('working_days')->default(5);
            $table->tinyInteger('daily_work_hours')->default(8);
            $table->smallInteger('holidays')->default(0);
            $table->string('working_zone')->nullable();
            $table->foreignId('project_type_id')->nullable()->constrained('project_types')->nullOnDelete();

            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            // $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            // $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
