<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_template_id')->constrained('project_templates')->onDelete('cascade');
            $table->foreignId('work_item_id')->constrained('work_items')->onDelete('cascade');
            $table->string('item_label')->nullable();
            $table->string('subproject_name')->nullable();
            $table->decimal('total_quantity', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('daily_quantity', 15, 2)->nullable();
            $table->decimal('estimated_daily_qty', 15, 2)->nullable();
            $table->integer('duration')->default(0);
            $table->integer('shift')->default(0);
            $table->integer('lag')->default(0);
            $table->unsignedBigInteger('predecessor')->nullable();
            $table->string('dependency_type')->nullable();
            $table->text('notes')->nullable();
            $table->integer('item_order')->default(0);
            $table->boolean('is_measurable')->default(true);
            $table->timestamps();
            
            $table->index('project_template_id');
            $table->index('work_item_id');
            $table->index('subproject_name');
            $table->index('item_order');
            
            // Foreign key for predecessor (self-referencing)
            $table->foreign('predecessor')->references('id')->on('template_items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_items');
    }
};
