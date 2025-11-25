<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */         
    public function run()
    {
        DB::table('employees')->insert([
            'name' => 'محمد عبد الله',
            'email' => 'mohamed@example.com',
            'password' => Hash::make('123'),
            'phone' => '01010101010',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'nationalId' => '1234567890',
            'marital_status' => 'متزوج',
            'education' => 'بكالوريوس',
            'information' => 'موظف في الشركة',
            'status' => 'مفعل',
            'country_id' => 1,
            'city_id' => 1,
            'state_id' => 1,
            'town_id' => 1,
            'job_id' => 1,
            'department_id' => 3,
            'date_of_hire' => '2025-01-01',
            'date_of_fire' => '2027-01-01',
            'image' => null,
            'shift_id' => 1,
            'salary' => 3000,
            'salary_type' => 'ساعات عمل فقط',
            'additional_hour_calculation' => 1.5,
            'finger_print_id' => '01',
            'finger_print_name' => 'محمد عبد الله',
            'created_at' => now(),
        ]);

        DB::table('employees')->insert([
            'name' => 'ess',
            'email' => 'ess@e.com',
            'password' => Hash::make('123'),
            'phone' => '123654798',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'nationalId' => '125478565',
            'marital_status' => 'متزوج',
            'education' => 'بكالوريوس',
            'information' => 'موظف في الشركة',
            'status' => 'مفعل',
            'country_id' => 1,
            'city_id' => 1,
            'state_id' => 1,
            'town_id' => 1,
            'job_id' => 1,
            'department_id' => 3,
            'date_of_hire' => '2025-01-01',
            'date_of_fire' => '2030-01-01',
            'image' => null,
            'shift_id' => 1,
            'salary' => 5000,
            'salary_type' => 'ساعات عمل و إضافي يومى',
            'additional_hour_calculation' => 1.5,
            'additional_day_calculation' => 1.5,
            'late_hour_calculation' => 1.5,
            'late_day_calculation' => 1.5,
            'finger_print_id' => '1254',
            'finger_print_name' => 'ess',
            'created_at' => now(),
        ]);
        
    }
}