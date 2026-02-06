<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('template_items', function (Blueprint $table) {
            $table->id();

            // القالب اللي البند تابع له
            $table->foreignId('project_template_id')
                  ->constrained('project_templates')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // البند الأساسي من work_items
            $table->foreignId('work_item_id')
                  ->constrained('work_items')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            // كمية افتراضية
            $table->decimal('default_quantity', 15, 3)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_items');
    }
};
