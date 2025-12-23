<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounts\database\seeders\AccHeadSeeder;
use Modules\Accounts\database\seeders\AccountsTypesSeeder;
use Modules\ActivityLog\database\seeders\ActivityLogPermissionsSeeder;
use Modules\App\database\seeders\AppPermissionsSeeder;
use Modules\Authorization\Database\Seeders\HRPermissionsSeeder;
use Modules\Authorization\Database\Seeders\PermissionSeeder;
use Modules\Authorization\Database\Seeders\PermissionSelectiveOptionsSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionSeeder;
use Modules\Branches\database\seeders\AttachUserToDefaultBranchSeeder;
use Modules\Branches\database\seeders\BranchesPermissionsSeeder;
use Modules\Branches\database\seeders\BranchSeeder;
use Modules\Checks\database\seeders\CheckPortfoliosPermissionsSeeder;
use Modules\Checks\database\seeders\ChecksPermissionsSeeder;
use Modules\CRM\Database\Seeders\CRMPermissionsSeeder;
use Modules\CRM\Database\Seeders\LeadStatusSeeder;
use Modules\Depreciation\database\seeders\DepreciationPermissionsSeeder;
use Modules\Fleet\database\seeders\FleetPermissionsSeeder;
use Modules\Inquiries\database\seeders\DiffcultyMatrixSeeder;
use Modules\Inquiries\database\seeders\InquiriesPermissionsSeeder;
use Modules\Inquiries\database\seeders\InquiriesRolesSeeder;
use Modules\Inquiries\database\seeders\PricingStatusPermissionsSeeder;
use Modules\Installments\database\seeders\InstallmentsPermissionsSeeder;
use Modules\Invoices\database\seeders\InvoiceDimensionsSeeder;
use Modules\Invoices\database\seeders\InvoiceTemplatesDiscountsPermissionsSeeder;
use Modules\Invoices\database\seeders\InvoiceTemplatesSeeder;
use Modules\Maintenance\database\seeders\MaintenancePermissionsSeeder;
use Modules\Manufacturing\database\seeders\ManufacturingPermissionsSeeder;
use Modules\MyResources\database\seeders\ResourcesPermissionsSeeder;
use Modules\Notifications\database\seeders\NotificationsPermissionsSeeder;
use Modules\POS\database\seeders\POSPermissionsSeeder;
use Modules\Quality\database\seeders\QualityModulePermissionsSeeder;
use Modules\Recruitment\database\seeders\RecruitmentPermissionsSeeder;
use Modules\Rentals\database\seeders\RentalsPermissionsSeeder;
use Modules\Reports\database\seeders\ReportsPermissionsSeeder;
use Modules\Services\database\seeders\ServicesPermissionsSeeder;
use Modules\Settings\Database\Seeders\AddNationalAddressAndTaxNumberSeeder;
use Modules\Settings\Database\Seeders\InvoiceOptionsSeeder;
use Modules\Settings\Database\Seeders\SettingSeeder;
use Modules\Settings\Database\Seeders\SettingsPermissionsSeeder;
use Modules\Settings\Database\Seeders\SystemSettingsSeeder;
use Modules\Shipping\Database\Seeders\ShippingPermissionsSeeder;
use Modules\Zatca\Database\Seeders\ZatcaPermissionsSeeder;

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
            ActivityLogPermissionsSeeder::class,
            ServicesPermissionsSeeder::class,
            DepreciationPermissionsSeeder::class,
            ReportsPermissionsSeeder::class,
            SettingsPermissionsSeeder::class,
            ZatcaPermissionsSeeder::class,
            NotificationsPermissionsSeeder::class,
            AppPermissionsSeeder::class,
            BranchesPermissionsSeeder::class,
            PurchaseDiscountMethodSeeder::class,
            VatAccountsSettingsSeeder::class,
            PurchaseDiscountMethodSeeder::class,
        ]);
    }
}
