<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('driver_id')->constrained()->onDelete('restrict');
            $table->foreignId('shipment_id')->constrained()->onDelete('restrict');
            $table->string('customer_name');
            $table->text('customer_address');
            $table->enum('delivery_status', ['pending', 'assigned', 'in_transit', 'delivered']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
