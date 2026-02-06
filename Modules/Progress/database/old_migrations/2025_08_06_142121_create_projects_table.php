<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('working_days')->default(5); // عدد أيام العمل الأسبوعية
            $table->tinyInteger('daily_work_hours')->default(8); // ساعات العمل اليومية
            $table->smallInteger('holidays')->default(0); // أيام الإجازة الرسمية
            $table->enum('status', ['active', 'completed', 'pending'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
