<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('shipping_company_id')->constrained()->onDelete('restrict');
            $table->string('customer_name');
            $table->text('customer_address');
            $table->decimal('weight', 8, 2);
            $table->enum('status', ['pending', 'in_transit', 'delivered']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}
