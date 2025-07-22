<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test', function (Blueprint $table) {
            $table->id();
            $table->integer('int1');
            $table->integer('int2');
            $table->string('var1');
            $table->string('var2');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test');
    }
};
