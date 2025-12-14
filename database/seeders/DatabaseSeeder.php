<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\POS\database\seeders\POSPermissionsSeeder;
use Modules\Fleet\database\seeders\FleetPermissionsSeeder;
use Modules\Checks\database\seeders\ChecksPermissionsSeeder;
use Modules\Rentals\database\seeders\RentalsPermissionsSeeder;
use Modules\Shipping\Database\Seeders\ShippingPermissionsSeeder;
use Modules\MyResources\database\seeders\ResourcesPermissionsSeeder;
use Modules\Quality\database\seeders\QualityModulePermissionsSeeder;
use Modules\Checks\database\seeders\CheckPortfoliosPermissionsSeeder;
use Modules\Maintenance\database\seeders\MaintenancePermissionsSeeder;
use Modules\Recruitment\database\seeders\RecruitmentPermissionsSeeder;
use Modules\Installments\database\seeders\InstallmentsPermissionsSeeder;
use Modules\CRM\Database\Seeders\{LeadStatusSeeder, CRMPermissionsSeeder};
use Modules\Manufacturing\database\seeders\ManufacturingPermissionsSeeder;
use Modules\Accounts\database\seeders\{AccHeadSeeder, AccountsTypesSeeder};
use Modules\Settings\Database\seeders\AddNationalAddressAndTaxNumberSeeder;
use Modules\Branches\database\seeders\{BranchSeeder, AttachUserToDefaultBranchSeeder};
use Modules\Settings\Database\Seeders\{SettingSeeder, InvoiceOptionsSeeder, SystemSettingsSeeder};
use Modules\Invoices\database\seeders\{InvoiceTemplatesSeeder, InvoiceDimensionsSeeder, InvoiceTemplatesDiscountsPermissionsSeeder};
use Modules\Authorization\Database\Seeders\{PermissionSeeder, HRPermissionsSeeder, RoleAndPermissionSeeder, PermissionSelectiveOptionsSeeder};
use Modules\Inquiries\database\seeders\{InquiriesRolesSeeder, DiffcultyMatrixSeeder, InquiriesPermissionsSeeder, PricingStatusPermissionsSeeder};

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
            HRPermissionsSeeder::class,
            RecruitmentPermissionsSeeder::class,
            PermissionSelectiveOptionsSeeder::class,
            InvoicesPermissionsSeeder::class,
            InvoiceTemplatesDiscountsPermissionsSeeder::class,
            ManufacturingPermissionsSeeder::class,
            ShippingPermissionsSeeder::class,
            PricingStatusPermissionsSeeder::class,
            ChecksPermissionsSeeder::class,
            CheckPortfoliosPermissionsSeeder::class,
            POSPermissionsSeeder::class,
            ResourcesPermissionsSeeder::class,
            QualityModulePermissionsSeeder::class,
            GiveAllPermissionsToAdminSeeder::class,
            MaintenancePermissionsSeeder::class,
            FleetPermissionsSeeder::class,
            AddNationalAddressAndTaxNumberSeeder::class,
        ]);
    }
}
