<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('work_item_category_id')->nullable()->constrained('work_item_categories')->onDelete('set null');
            $table->string('unit')->nullable();
            $table->decimal('estimated_daily_qty', 15, 2)->nullable();
            $table->enum('shift', ['single', 'double', 'triple'])->default('single');
            $table->integer('order')->default(0);
            $table->foreignId('item_status_id')->nullable()->constrained('item_statuses')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('work_item_category_id');
            $table->index('item_status_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};
