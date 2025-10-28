<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->json('visible_columns');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('column_widths')->nullable();
            $table->json('column_order')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_type_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->integer('invoice_type');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('invoice_templates')
                ->onDelete('cascade');

            $table->unique(['template_id', 'invoice_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_type_templates');
        Schema::dropIfExists('invoice_templates');
    }
};
