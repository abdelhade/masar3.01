<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionSeeder;
use Modules\CRM\Database\Seeders\LeadStatusSeeder;
use Modules\Settings\Database\Seeders\SettingSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AccHeadSeeder::class,
            UpdateAccHeadSeeder::class,
            ProTypesSeeder::class,
            CostCentersSeeder::class,
            NoteSeeder::class,
            NoteDetailsSeeder::class,
            UnitSeeder::class,
            PriceSeeder::class,
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            TownSeeder::class,
            EmployeesJobSeeder::class,
            ShiftSeeder::class,
            SettingSeeder::class,
            ItemSeeder::class,
            LeadStatusSeeder::class,
            KpiSeeder::class,
            EmployeeSeeder::class,
            ContractTypeSeeder::class,
            AttendanceSeeder::class,
            CvSeeder::class,
            LeaveTypeSeeder::class,
            RentalsProTypesSeeder::class
        ]);
    }
}
