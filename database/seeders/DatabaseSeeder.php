<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HR\database\seeders\HRDatabaseSeeder;
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
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\Shipping\Database\Seeders\ShippingDatabaseSeeder;
use Modules\Inquiries\database\seeders\InquiriesDatabaseSeeder;
use Modules\MyResources\database\seeders\ResourcesDatabaseSeeder;
use Modules\ActivityLog\database\seeders\ActivityLogDatabaseSeeder;
use Modules\Maintenance\database\seeders\MaintenanceDatabaseSeeder;
use Modules\Recruitment\database\seeders\RecruitmentDatabaseSeeder;
use Modules\Depreciation\database\seeders\DepreciationDatabaseSeeder;
use Modules\Installments\database\seeders\InstallmentsDatabaseSeeder;
use Modules\Manufacturing\database\seeders\ManufacturingDatabaseSeeder;
use Modules\Authorization\Database\Seeders\RoleAndPermissionDatabaseSeeder;

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
        $this->call(BranchesDatabaseSeeder::class);
        $this->call(AccountsDatabaseSeeder::class);
        $this->call(ActivityLogDatabaseSeeder::class);
        $this->call(AppDatabaseSeeder::class);
        $this->call(RoleAndPermissionDatabaseSeeder::class);
        $this->call(ChecksDatabaseSeeder::class);
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
        $this->call(HRDatabaseSeeder::class);
        $this->call(RentalsDatabaseSeeder::class);
        $this->call(ReportDatabaseSeeder::class);
        // $this->call(ServicesDatabaseSeeder::class);
        $this->call(ShippingDatabaseSeeder::class);
        $this->call(ZatcaDatabaseSeeder::class);

        $this->call([
            UpdateAccHeadAccTypeSeeder::class,
            ProTypesSeeder::class,
            NoteSeeder::class,
            NoteDetailsSeeder::class,
            UnitSeeder::class,
            PriceSeeder::class,
            VaribalSeeder::class,
            GiveAllPermissionsToAdminSeeder::class,
        ]);
        
    }
}
