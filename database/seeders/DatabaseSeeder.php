<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Database\Seeders\LeadStatusSeeder;
use Modules\Settings\Database\Seeders\SettingSeeder;
use Modules\Settings\Database\Seeders\SystemSettingsSeeder;
use Modules\Inquiries\database\seeders\DiffcultyMatrixSeeder;
use Modules\Invoices\database\seeders\InvoiceTemplatesSeeder;
use Modules\Invoices\database\seeders\InvoiceDimensionsSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionSeeder;
use Modules\Branches\database\seeders\{AttachUserToDefaultBranchSeeder, BranchSeeder};

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            AccHeadSeeder::class,
            AccountsTypesSeeder::class,
            UpdateAccHeadAccTypeSeeder::class,
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
            InvoiceTemplatesSeeder::class,
            SystemSettingsSeeder::class,
            InvoiceDimensionsSeeder::class,
            LeadStatusSeeder::class,
            KpiSeeder::class,
            EmployeeSeeder::class,
            ContractTypeSeeder::class,
            AttendanceSeeder::class,
            CvSeeder::class,
            LeaveTypeSeeder::class,
            AttachUserToDefaultBranchSeeder::class,
            DiffcultyMatrixSeeder::class,
            VaribalSeeder::class,
        ]);
    }
}
