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
            $table->string('cname', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('phone2', 150)->nullable();
            $table->string('address', 250)->nullable();
            $table->string('address2', 150)->nullable();
            $table->string('address3', 150)->nullable();
            $table->integer('city')->nullable();
            $table->double('height')->nullable();
            $table->double('weight')->nullable();
            $table->date('dateofbirth')->nullable();
            $table->string('ref', 20)->nullable();
            $table->string('diseses', 200)->nullable();
            $table->string('info', 200)->nullable();
            $table->string('imgs', 250)->nullable();
            $table->string('jop', 50)->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->string('drugs', 250)->nullable();
            $table->string('seriousdes', 250)->nullable();
            $table->string('familydes', 250)->nullable();
            $table->string('allergy', 250)->nullable();
            $table->string('temp', 9)->nullable();
            $table->string('pressure', 9)->nullable();
            $table->string('diabetes', 9)->nullable();
            $table->string('brate', 9)->nullable();
            $table->boolean('isdeleted')->default(0);
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
