<?php

use Modules\Rentals\Enums\UnitStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')
                ->constrained('rentals_buildings')
                ->onDelete('cascade');
            $table->string('name');
            $table->integer('floor')->nullable();
            $table->decimal('area', 10, 2)->nullable(); // المساحة بالمتر
            $table->tinyInteger('status')->default(UnitStatus::AVAILABLE->value);
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals_units');
    }
};
