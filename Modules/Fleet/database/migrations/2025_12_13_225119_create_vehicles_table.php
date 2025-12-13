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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('plate_number')->unique();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->decimal('current_mileage', 10, 2)->default(0);
            $table->string('status')->default('available');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
