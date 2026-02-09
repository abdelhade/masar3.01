<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->foreignId('reporter_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->date('due_date')->nullable();
            $table->date('deadline')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
            $table->index('status');
            $table->index('reporter_id');
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
