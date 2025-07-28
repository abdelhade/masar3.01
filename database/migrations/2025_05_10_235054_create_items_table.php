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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->required()->unique();
            $table->unsignedBigInteger('code')->required()->unique();
            $table->string('info', 250)->nullable();
            $table->timestamps();
            $table->integer('tenant')->default(0);
            $table->integer('branch')->default(0);
            $table->double('average_cost', 12, 2)->default(0);
            $table->integer('min_order_quantity')->default(0);
            $table->integer('max_order_quantity')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
