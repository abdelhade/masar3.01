<?php

namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('accounts_types')->updateOrInsert(['id' => 1], ['name' => 'clients']);
        DB::table('accounts_types')->updateOrInsert(['id' => 2], ['name' => 'suppliers']);
        DB::table('accounts_types')->updateOrInsert(['id' => 3], ['name' => 'funds']);
        DB::table('accounts_types')->updateOrInsert(['id' => 4], ['name' => 'banks']);
        DB::table('accounts_types')->updateOrInsert(['id' => 5], ['name' => 'employees']);
        DB::table('accounts_types')->updateOrInsert(['id' => 6], ['name' => 'warhouses']);
        DB::table('accounts_types')->updateOrInsert(['id' => 7], ['name' => 'expenses']);
        DB::table('accounts_types')->updateOrInsert(['id' => 8], ['name' => 'revenues']);
        DB::table('accounts_types')->updateOrInsert(['id' => 9], ['name' => 'creditors']);
        DB::table('accounts_types')->updateOrInsert(['id' => 10], ['name' => 'debtors']);
        DB::table('accounts_types')->updateOrInsert(['id' => 11], ['name' => 'partners']);
        DB::table('accounts_types')->updateOrInsert(['id' => 12], ['name' => 'current-partners']);
        DB::table('accounts_types')->updateOrInsert(['id' => 13], ['name' => 'assets']);
        DB::table('accounts_types')->updateOrInsert(['id' => 14], ['name' => 'rentables']);
        DB::table('accounts_types')->updateOrInsert(['id' => 15], ['name' => 'accumulated-depreciations']);
        DB::table('accounts_types')->updateOrInsert(['id' => 16], ['name' => 'depreciation-expenses']);
        DB::table('accounts_types')->updateOrInsert(['id' => 17], ['name' => 'check-portfolios-incoming']); // حافظات حافظة اوراق قبض
        DB::table('accounts_types')->updateOrInsert(['id' => 18], ['name' => 'check-portfolios-outgoing']); // حافظات حافظة اوراق دفع
        
    }
}


