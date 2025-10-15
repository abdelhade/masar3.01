<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('pro_types', function (Blueprint $table) {
        $table->id();
        $table->string('pname', 200)->nullable()->unique();
        $table->string('ptext', 200)->nullable()->unique();
        $table->string('ptype', 200)->nullable();
        $table->string('info', 200)->nullable();
        $table->timestamps();
        $table->tinyInteger('isdeleted')->default(0);
        $table->integer('tenant')->default(0);
        $table->integer('branch')->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_types');
    }
};
