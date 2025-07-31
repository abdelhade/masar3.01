<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\AccHeadSeeder;
use Database\Seeders\ProTypesSeeder;
use Database\Seeders\CostCentersSeeder;
use Modules\Authorization\database\seeders\RoleAndPermissionSeeder;
use Modules\CRM\database\seeders\LeadStatusSeeder;
use Modules\Settings\database\seeders\SettingSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AccHeadSeeder::class,
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
        ]);
    }
}
