<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_work_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries')->onDelete('cascade');
            $table->foreignId('work_type_id')->constrained('work_types')->onDelete('cascade');
            $table->json('hierarchy_path')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            // Index للأداء
            $table->index(['inquiry_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_work_type');
    }
};
