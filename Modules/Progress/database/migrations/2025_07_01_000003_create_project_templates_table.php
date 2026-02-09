<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('project_type_id')->nullable()->constrained('project_types')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->string('working_zone')->nullable();
            $table->tinyInteger('working_days')->default(5);
            $table->tinyInteger('daily_work_hours')->default(8);
            $table->string('weekly_holidays')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};
