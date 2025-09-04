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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('id')->autoIncrement();
            $table->string('cname')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('company')->nullable();

            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id')->nullable();

            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_relation')->nullable();

            $table->string('info')->nullable();
            $table->string('job')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->boolean('isdeleted')->default(0);
            $table->boolean('is_active')->default(0);
            $table->integer('created_by')->default(0);

            $table->enum('type', ['person', 'company'])->default('person');

            $table->integer('tenant')->default(0);
            $table->integer('branch')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
