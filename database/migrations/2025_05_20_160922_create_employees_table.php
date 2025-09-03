<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{

    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('image')->nullable();

            $table->string('position')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('user_id')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->enum('gender', array('male', 'female'))->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationalId')->nullable()->unique();
            $table->enum('marital_status', array('متزوج', 'غير متزوج', 'مطلق', 'أرمل'))->nullable();
            $table->enum('education', array('دبلوم', 'بكالوريوس', 'ماجستير', 'دكتوراه'))->nullable();
            $table->text('information')->nullable();
            $table->enum('status', array('مفعل', 'معطل'))->default('مفعل');
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('restrict');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('restrict');
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('restrict');
            $table->foreignId('town_id')->nullable()->constrained('towns')->onDelete('restrict');
            $table->foreignId('job_id')->nullable()->constrained('employees_jobs')->onDelete('restrict');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('restrict');
            $table->date('date_of_hire')->nullable();
            $table->date('date_of_fire')->nullable();
            $table->enum('job_level', array('مبتدئ', 'متوسط', 'محترف'))->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->integer('finger_print_id')->nullable()->unique();
            $table->string('finger_print_name')->nullable()->unique();
            $table->enum('salary_type', array('ساعات عمل فقط', 'ساعات عمل و إضافي يومى', 'ساعات عمل و إضافي للمده', 'حضور فقط', 'إنتاج فقط'))->nullable();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('restrict');
            $table->string('password')->nullable();
            $table->decimal('additional_hour_calculation', 10, 2)->nullable();
            $table->decimal('additional_day_calculation', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('employees');
    }
}
