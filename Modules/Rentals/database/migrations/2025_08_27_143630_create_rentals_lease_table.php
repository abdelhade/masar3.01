<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Rentals\Enums\LeaseStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');

            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('acc_head')->onDelete('cascade');

            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rent_amount', 12, 2);

            $table->unsignedBigInteger('acc_id')->nullable();
            $table->foreign('acc_id')->references('id')->on('acc_head')->onDelete('set null');

            $table->unsignedTinyInteger('status')->default(LeaseStatus::PENDING->value);
            $table->text('notes')->nullable();
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
