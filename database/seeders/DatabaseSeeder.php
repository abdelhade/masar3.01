<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Modules\CRM\Database\Seeders\LeadStatusSeeder;
use Modules\App\database\seeders\AppDatabaseSeeder;
// use Modules\Accounts\database\seeders\AccHeadSeeder;
// use Modules\Branches\database\seeders\BranchSeeder;
// use Modules\App\database\seeders\AppPermissionsSeeder;
// use Modules\CRM\Database\Seeders\CRMPermissionsSeeder;
// use Modules\POS\database\seeders\POSPermissionsSeeder;
// use Modules\Accounts\database\seeders\AccountsTypesSeeder;
// use Modules\Fleet\database\seeders\FleetPermissionsSeeder;
// use Modules\Zatca\Database\Seeders\ZatcaPermissionsSeeder;
// use Modules\Settings\Database\Seeders\InvoiceOptionsSeeder;
// use Modules\Settings\Database\Seeders\SystemSettingsSeeder;
use Modules\CRM\Database\Seeders\CRMDatabaseSeeder;
// use Modules\Checks\database\seeders\ChecksPermissionsSeeder;
use Modules\POS\database\seeders\POSDatabaseSeeder;
use Modules\Settings\Database\Seeders\SettingSeeder;
use Modules\Fleet\database\seeders\FleetDatabaseSeeder;
// use Modules\Reports\database\seeders\ReportPermissionsSeeder;
// use Modules\Invoices\database\seeders\InvoiceDimensionsSeeder;
// use Modules\Rentals\database\seeders\RentalsPermissionsSeeder;
// use Modules\Reports\database\seeders\ReportsPermissionsSeeder;
use Modules\Zatca\Database\Seeders\ZatcaDatabaseSeeder;
// use Modules\Branches\database\seeders\BranchesPermissionsSeeder;
// use Modules\Services\database\seeders\ServicesPermissionsSeeder;
// use Modules\Settings\Database\Seeders\SettingsPermissionsSeeder;
// use Modules\Shipping\Database\Seeders\ShippingPermissionsSeeder;
// use Modules\Inquiries\database\seeders\InquiriesPermissionsSeeder;
// use Modules\Authorization\Database\Seeders\RoleAndPermissionSeeder;
// use Modules\MyResources\database\seeders\ResourcesPermissionsSeeder;
// use Modules\Quality\database\seeders\QualityModulePermissionsSeeder;ذ
use Modules\Checks\database\seeders\ChecksDatabaseSeeder;
// use Modules\ActivityLog\database\seeders\ActivityLogPermissionsSeeder;
// use Modules\Branches\database\seeders\AttachUserToDefaultBranchSeeder;
// use Modules\Inquiries\database\seeders\PricingStatusPermissionsSeeder;
// use Modules\Maintenance\database\seeders\MaintenancePermissionsSeeder;
// use Modules\Recruitment\database\seeders\RecruitmentPermissionsSeeder;
// use Modules\Depreciation\database\seeders\DepreciationPermissionsSeeder;
// use Modules\Installments\database\seeders\InstallmentsPermissionsSeeder;
// use Modules\Manufacturing\database\seeders\ManufacturingPermissionsSeeder;
// use Modules\Notifications\database\seeders\NotificationsPermissionsSeeder;
// use Modules\Settings\Database\Seeders\AddNationalAddressAndTaxNumberSeeder;
use Modules\Reports\database\seeders\ReportDatabaseSeeder;
use Modules\Quality\database\seeders\QualityDatabaseSeeder;
use Modules\Rentals\database\seeders\RentalsDatabaseSeeder;
// use Modules\Authorization\Database\Seeders\PermissionSeeder;
use Modules\Inquiries\database\seeders\InquiriesRolesSeeder;
use Modules\Invoices\database\seeders\InvoiceDatabaseSeeder;
use Modules\Accounts\database\seeders\AccountsDatabaseSeeder;
use Modules\Branches\database\seeders\BranchesDatabaseSeeder;
use Modules\Inquiries\database\seeders\DiffcultyMatrixSeeder;
// use Modules\Invoices\database\seeders\InvoiceTemplatesSeeder;
use Modules\Progress\database\seeders\ProgressDatabaseSeeder;
use Modules\Services\database\seeders\ServicesDatabaseSeeder;
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\Shipping\Database\Seeders\ShippingDatabaseSeeder;
use Modules\Authorization\Database\Seeders\HRPermissionsSeeder;
use Modules\Inquiries\database\seeders\InquiriesDatabaseSeeder;
use Modules\MyResources\database\seeders\ResourcesDatabaseSeeder;
use Modules\ActivityLog\database\seeders\ActivityLogDatabaseSeeder;
use Modules\Maintenance\database\seeders\MaintenanceDatabaseSeeder;
use Modules\Recruitment\database\seeders\RecruitmentDatabaseSeeder;
use Modules\Checks\database\seeders\CheckPortfoliosPermissionsSeeder;
use Modules\Depreciation\database\seeders\DepreciationDatabaseSeeder;
use Modules\Installments\database\seeders\InstallmentsDatabaseSeeder;
use Modules\Manufacturing\database\seeders\ManufacturingDatabaseSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionDatabaseSeeder;
use Modules\Authorization\Database\Seeders\PermissionSelectiveOptionsSeeder;
// use Modules\Invoices\database\seeders\InvoiceTemplatesDiscountsPermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
        ]);

        $this->call(AccountsDatabaseSeeder::class);
        $this->call(ActivityLogDatabaseSeeder::class);
        $this->call(AppDatabaseSeeder::class);
        $this->call(RoleAndPermissionDatabaseSeeder::class);
        $this->call(ChecksDatabaseSeeder::class);
        $this->call(BranchesDatabaseSeeder::class);
        $this->call(CRMDatabaseSeeder::class);
        $this->call(DepreciationDatabaseSeeder::class);
        $this->call(FleetDatabaseSeeder::class);
        $this->call(InquiriesDatabaseSeeder::class);
        $this->call(InstallmentsDatabaseSeeder::class);
        $this->call(SettingsDatabaseSeeder::class);
        $this->call(InvoiceDatabaseSeeder::class);
        $this->call(MaintenanceDatabaseSeeder::class);
        $this->call(ManufacturingDatabaseSeeder::class);
        $this->call(ResourcesDatabaseSeeder::class);
        $this->call(POSDatabaseSeeder::class);
        $this->call(ProgressDatabaseSeeder::class);
        $this->call(QualityDatabaseSeeder::class);
        $this->call(RecruitmentDatabaseSeeder::class);
        $this->call(RentalsDatabaseSeeder::class);
        $this->call(ReportDatabaseSeeder::class);
        $this->call(ServicesDatabaseSeeder::class);
        $this->call(ShippingDatabaseSeeder::class);
        $this->call(ZatcaDatabaseSeeder::class);

        $this->call([

            // BranchSeeder::class,
            // AccHeadSeeder::class,
            // AccountsTypesSeeder::class,
            UpdateAccHeadAccTypeSeeder::class,
            ProTypesSeeder::class,
            CostCentersSeeder::class,
            NoteSeeder::class,
            NoteDetailsSeeder::class,
            UnitSeeder::class,
            PriceSeeder::class,
            // RoleAndPermissionSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            TownSeeder::class,
            EmployeesJobSeeder::class,
            ShiftSeeder::class,
            // SettingSeeder::class,
            // ItemSeeder::class,
            // InvoiceOptionsSeeder::class,
            // InvoiceTemplatesSeeder::class,
            // InvoiceDimensionsSeeder::class,
            // LeadStatusSeeder::class,
            KpiSeeder::class,
            EmployeeSeeder::class,
            ContractTypeSeeder::class,
            AttendanceSeeder::class,
            CvSeeder::class,
            LeaveTypeSeeder::class,
            // AttachUserToDefaultBranchSeeder::class,
            // DiffcultyMatrixSeeder::class,
            VaribalSeeder::class,
            // InquiriesPermissionsSeeder::class,
            // InquiriesRolesSeeder::class,
            // CRMPermissionsSeeder::class,
            // RentalsPermissionsSeeder::class,
            // InstallmentsPermissionsSeeder::class,
            // PermissionSeeder::class,
            HRPermissionsSeeder::class,
            // RecruitmentPermissionsSeeder::class,
            // PermissionSelectiveOptionsSeeder::class,
            // InvoicesPermissionsSeeder::class,
            // InvoiceTemplatesDiscountsPermissionsSeeder::class,
            // ManufacturingPermissionsSeeder::class,
            // ShippingPermissionsSeeder::class,
            // PricingStatusPermissionsSeeder::class,
            // ChecksPermissionsSeeder::class,
            // CheckPortfoliosPermissionsSeeder::class,
            // POSPermissionsSeeder::class,
            // ResourcesPermissionsSeeder::class,
            // QualityModulePermissionsSeeder::class,
            // MaintenancePermissionsSeeder::class,
            // FleetPermissionsSeeder::class,
            // ActivityLogPermissionsSeeder::class,
            // ServicesPermissionsSeeder::class,
            // DepreciationPermissionsSeeder::class,
            //  ReportsPermissionsSeeder::class,
            // SettingsPermissionsSeeder::class,
            // ZatcaPermissionsSeeder::class,
            // AppPermissionsSeeder::class,
            // BranchesPermissionsSeeder::class,
            PurchaseDiscountMethodSeeder::class,
            VatAccountsSettingsSeeder::class,
            PurchaseDiscountMethodSeeder::class,
            // ReportPermissionsSeeder::class,

            GiveAllPermissionsToAdminSeeder::class,
        ]);
    }
}
