<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Database\Seeders\LeadStatusSeeder;
use Modules\Branches\database\seeders\BranchSeeder;
use Modules\Accounts\database\seeders\AccHeadSeeder;
use Modules\Settings\Database\Seeders\SettingSeeder;
use Modules\App\database\seeders\AppPermissionsSeeder;
use Modules\CRM\Database\Seeders\CRMPermissionsSeeder;
use Modules\POS\database\seeders\POSPermissionsSeeder;
use Modules\Accounts\database\seeders\AccountsTypesSeeder;
use Modules\Fleet\database\seeders\FleetPermissionsSeeder;
use Modules\Zatca\Database\Seeders\ZatcaPermissionsSeeder;
use Modules\Settings\Database\Seeders\InvoiceOptionsSeeder;
use Modules\Settings\Database\Seeders\SystemSettingsSeeder;
use Modules\Authorization\Database\Seeders\PermissionSeeder;
use Modules\Checks\database\seeders\ChecksPermissionsSeeder;
use Modules\Inquiries\database\seeders\InquiriesRolesSeeder;
use Modules\Inquiries\database\seeders\DiffcultyMatrixSeeder;
use Modules\Invoices\database\seeders\InvoiceTemplatesSeeder;
use Modules\Reports\database\seeders\ReportPermissionsSeeder;
use Modules\Invoices\database\seeders\InvoiceDimensionsSeeder;
use Modules\Rentals\database\seeders\RentalsPermissionsSeeder;
use Modules\Reports\database\seeders\ReportsPermissionsSeeder;
use Modules\Authorization\Database\Seeders\HRPermissionsSeeder;
use Modules\Branches\database\seeders\BranchesPermissionsSeeder;
use Modules\Services\database\seeders\ServicesPermissionsSeeder;
use Modules\Settings\Database\Seeders\InvoiceOptionsSeeder;
use Modules\Settings\Database\Seeders\SettingSeeder;

use Modules\Settings\Database\Seeders\SettingsPermissionsSeeder;
use Modules\Shipping\Database\Seeders\ShippingPermissionsSeeder;
use Modules\Inquiries\database\seeders\InquiriesPermissionsSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionSeeder;
use Modules\MyResources\database\seeders\ResourcesPermissionsSeeder;
use Modules\Quality\database\seeders\QualityModulePermissionsSeeder;
use Modules\Checks\database\seeders\CheckPortfoliosPermissionsSeeder;
use Modules\ActivityLog\database\seeders\ActivityLogPermissionsSeeder;
use Modules\Branches\database\seeders\AttachUserToDefaultBranchSeeder;
use Modules\Inquiries\database\seeders\PricingStatusPermissionsSeeder;
use Modules\Maintenance\database\seeders\MaintenancePermissionsSeeder;
use Modules\Recruitment\database\seeders\RecruitmentPermissionsSeeder;
use Modules\Depreciation\database\seeders\DepreciationPermissionsSeeder;
use Modules\Installments\database\seeders\InstallmentsPermissionsSeeder;
use Modules\Manufacturing\database\seeders\ManufacturingPermissionsSeeder;
use Modules\Notifications\database\seeders\NotificationsPermissionsSeeder;
use Modules\Settings\Database\Seeders\AddNationalAddressAndTaxNumberSeeder;
use Modules\Authorization\Database\Seeders\PermissionSelectiveOptionsSeeder;
use Modules\Invoices\database\seeders\InvoiceTemplatesDiscountsPermissionsSeeder;

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
            ActivityLogPermissionsSeeder::class,
            ServicesPermissionsSeeder::class,
            DepreciationPermissionsSeeder::class,
            //  ReportsPermissionsSeeder::class,
            SettingsPermissionsSeeder::class,
            ZatcaPermissionsSeeder::class,
            AppPermissionsSeeder::class,
            BranchesPermissionsSeeder::class,
            PurchaseDiscountMethodSeeder::class,
            VatAccountsSettingsSeeder::class,
            PurchaseDiscountMethodSeeder::class,
            ReportPermissionsSeeder::class,

        ]);
    }
}
