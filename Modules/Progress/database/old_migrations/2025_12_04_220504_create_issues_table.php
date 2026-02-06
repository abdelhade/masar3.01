<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     * Create issues table for issue management system
     */
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->enum('status', ['New', 'In Progress', 'Testing', 'Closed'])->default('New');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('module')->nullable(); // Module/component where issue occurs
            $table->text('reproduce_steps')->nullable(); // Steps to reproduce the issue
            $table->date('deadline')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index('project_id');
            $table->index('status');
            $table->index('priority');
            $table->index('reporter_id');
            $table->index('assigned_to');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
