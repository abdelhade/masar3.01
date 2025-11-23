<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->enum('document_type', ['image', 'certificate', 'manual', 'warranty', 'invoice', 'other'])->default('other');
            $table->string('file_path');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['resource_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_documents');
    }
};

