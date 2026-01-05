<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\App\database\seeders\AppDatabaseSeeder;
use Modules\CRM\Database\Seeders\CRMDatabaseSeeder;
use Modules\POS\database\seeders\POSDatabaseSeeder;
use Modules\Fleet\database\seeders\FleetDatabaseSeeder;
use Modules\Zatca\Database\Seeders\ZatcaDatabaseSeeder;
use Modules\Checks\database\seeders\ChecksDatabaseSeeder;
use Modules\Reports\database\seeders\ReportDatabaseSeeder;
use Modules\Quality\database\seeders\QualityDatabaseSeeder;
use Modules\Rentals\database\seeders\RentalsDatabaseSeeder;
use Modules\Invoices\database\seeders\InvoiceDatabaseSeeder;
use Modules\Accounts\database\seeders\AccountsDatabaseSeeder;
use Modules\Branches\database\seeders\BranchesDatabaseSeeder;
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
use Modules\Depreciation\database\seeders\DepreciationDatabaseSeeder;
use Modules\Installments\database\seeders\InstallmentsDatabaseSeeder;
use Modules\Manufacturing\database\seeders\ManufacturingDatabaseSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionDatabaseSeeder;
use Modules\HR\Database\Seeders\AttendanceSeeder;
use Modules\HR\Database\Seeders\CitySeeder;
use Modules\HR\Database\Seeders\ContractTypeSeeder;
use Modules\HR\Database\Seeders\CostCentersSeeder;
use Modules\HR\Database\Seeders\CountrySeeder;
use Modules\HR\Database\Seeders\CvSeeder;
use Modules\HR\Database\Seeders\DepartmentSeeder;
use Modules\HR\Database\Seeders\EmployeeSeeder;
use Modules\HR\Database\Seeders\EmployeesJobSeeder;
use Modules\HR\Database\Seeders\KpiSeeder;
use Modules\HR\Database\Seeders\LeaveTypeSeeder;
use Modules\HR\Database\Seeders\ShiftSeeder;
use Modules\HR\Database\Seeders\StateSeeder;
use Modules\HR\Database\Seeders\TownSeeder;

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
        // $this->call(ServicesDatabaseSeeder::class);
        $this->call(ShippingDatabaseSeeder::class);
        $this->call(ZatcaDatabaseSeeder::class);

        $this->call([
            UpdateAccHeadAccTypeSeeder::class,
            ProTypesSeeder::class,
            CostCentersSeeder::class,
            NoteSeeder::class,
            NoteDetailsSeeder::class,
            UnitSeeder::class,
            PriceSeeder::class,
            DepartmentSeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            TownSeeder::class,
            EmployeesJobSeeder::class,
            ShiftSeeder::class,
            KpiSeeder::class,
            EmployeeSeeder::class,
            ContractTypeSeeder::class,
            AttendanceSeeder::class,
            CvSeeder::class,
            LeaveTypeSeeder::class,
            VaribalSeeder::class,
            HRPermissionsSeeder::class,
            GiveAllPermissionsToAdminSeeder::class,
        ]);
    }
}
