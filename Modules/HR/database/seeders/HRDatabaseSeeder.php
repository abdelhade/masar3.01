<?php

namespace Modules\HR\database\seeders;

use Illuminate\Database\Seeder;

class HRDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            HRPermissionsSeeder::class,
            AttendanceProcessingTestSeeder::class,
            AttendanceSeeder::class,
            CitySeeder::class,
            ContractTypeSeeder::class,
            CountrySeeder::class,
            CreateTestAttendanceData::class,
            CvSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            EmployeesJobSeeder::class,
            KpiSeeder::class,
            LeaveTypeSeeder::class,
            ShiftSeeder::class,
            StateSeeder::class,
            TownSeeder::class,
            AttendanceTestDataSeeder::class,
        ]);
    }
}
