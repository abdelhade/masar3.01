<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Modules\HR\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
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
            ],
            [
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
            ],
        ];

        foreach ($employees as $employeeData) {
            // Filter out columns that don't exist in the table
            $filteredData = $this->filterColumns($employeeData);

            Employee::firstOrCreate(
                ['email' => $filteredData['email']],
                $filteredData
            );
        }
    }

    /**
     * Filter out columns that don't exist in the employees table.
     */
    private function filterColumns(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (Schema::hasColumn('employees', $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
