<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Database\Seeders\LeadStatusSeeder;
use Modules\CRM\Database\Seeders\CRMPermissionsSeeder;
use Modules\Authorization\Database\Seeders\PermissionSeeder;
use Modules\Rentals\database\seeders\RentalsPermissionsSeeder;
use Modules\Shipping\Database\Seeders\ShippingPermissionsSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionSeeder;
use Modules\Installments\database\seeders\InstallmentsPermissionsSeeder;
use Modules\Manufacturing\database\seeders\ManufacturingPermissionsSeeder;
use Modules\Accounts\database\seeders\{AccHeadSeeder, AccountsTypesSeeder};
use Modules\Authorization\Database\Seeders\PermissionSelectiveOptionsSeeder;
use Modules\Invoices\database\seeders\InvoiceTemplatesDiscountsPermissionsSeeder;
use Modules\Branches\database\seeders\{AttachUserToDefaultBranchSeeder, BranchSeeder};
use Modules\Invoices\database\seeders\{InvoiceTemplatesSeeder, InvoiceDimensionsSeeder};
use Modules\Settings\Database\Seeders\{SettingSeeder, InvoiceOptionsSeeder, SystemSettingsSeeder};
use Modules\Inquiries\database\seeders\{InquiriesRolesSeeder, DiffcultyMatrixSeeder, InquiriesPermissionsSeeder};
use Modules\Authorization\Database\Seeders\PermissionSeeder;
use Modules\Authorization\Database\Seeders\PermissionSelectiveOptionsSeeder;


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
            // ItemSeeder::class,
            InvoiceOptionsSeeder::class,
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
            InquiriesPermissionsSeeder::class,
            InquiriesRolesSeeder::class,
            CRMPermissionsSeeder::class,
            RentalsPermissionsSeeder::class,
            InstallmentsPermissionsSeeder::class,
            PermissionSeeder::class,
            PermissionSelectiveOptionsSeeder::class,
            InvoicesPermissionsSeeder::class,
            InvoiceTemplatesDiscountsPermissionsSeeder::class,
            ManufacturingPermissionsSeeder::class,
            ShippingPermissionsSeeder::class,
        ]);
    }
}
