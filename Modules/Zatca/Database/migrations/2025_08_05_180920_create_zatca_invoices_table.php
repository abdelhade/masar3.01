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
        Schema::create('zatca_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->timestamp('invoice_date');
            $table->string('customer_name');
            $table->string('customer_vat')->nullable();
            $table->text('customer_address')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('SAR');
            $table->string('invoice_type')->default('388');
            $table->longText('xml_content')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('zatca_status')->default('draft');
            $table->string('zatca_uuid')->nullable();
            $table->string('zatca_hash')->nullable();
            $table->json('zatca_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zatca_invoices');
    }
};
