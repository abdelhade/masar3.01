<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_relationships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('related_contact_id');
            $table->string('relationship_type')->default('general');
            $table->timestamps();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();

            $table->foreign('related_contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();

            $table->unique(['contact_id', 'related_contact_id'], 'unique_contact_relationship');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_relationships');
    }
};
