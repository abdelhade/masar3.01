<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_printer_station', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade');
            $table->foreignId('printer_station_id')
                ->constrained('kitchen_printer_stations')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['category_id', 'printer_station_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_printer_station');
    }
};
