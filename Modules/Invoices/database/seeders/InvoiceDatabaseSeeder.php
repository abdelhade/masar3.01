<?php

namespace Modules\Invoices\database\seeders;

use Illuminate\Database\Seeder;

class InvoiceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            InvoiceTemplatesDiscountsPermissionsSeeder::class,
            InvoiceTemplatesSeeder::class,
            InvoiceOptionsSeeder::class,
            InvoicesPermissionsSeeder::class,
            InvoiceDimensionsSeeder::class,
        ]);
    }
}
