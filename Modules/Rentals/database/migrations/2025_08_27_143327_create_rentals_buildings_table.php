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
        Schema::create('rentals_buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->integer('floors')->nullable();

            $table->unsignedBigInteger('acc_id')->nullable();
            $table->foreign('acc_id')->references('id')->on('acc_head')->onDelete('set null');

            $table->decimal('area', 10, 2)->nullable(); // المساحة الكلية بالمتر
            $table->text('details')->nullable();

            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals_buildings');
    }
};
