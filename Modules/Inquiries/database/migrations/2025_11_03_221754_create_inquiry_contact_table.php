<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inquiry_contact', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_id');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->foreign('inquiry_id')->references('id')->on('inquiries')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('inquiries_roles')->cascadeOnDelete();

            $table->unique(['inquiry_id', 'contact_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_contact');
    }
};
