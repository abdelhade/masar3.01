<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateAccHeadAccTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Map code prefixes to accounts_types.name
        $prefixToType = [
            '1103' => 'clients',
            '2101' => 'suppliers',
            '1101' => 'funds',
            '1102' => 'banks',
            '5'   => 'expenses',
            '42'   => 'revenues',
            '2104' => 'creditors',
            '1106' => 'debtors',
            '31'   => 'partners',
            '2108'   => 'current-partners',
            '12'   => 'assets',
            '2102' => 'employees',
            '1104' => 'warhouses',
            '1202' => 'rentables',
        ];

        // Load accounts_types name=>id map
        $types = DB::table('accounts_types')->pluck('id', 'name');

        // Fetch all accounts to update
        $accounts = DB::table('acc_head')->select('id', 'code')->get();

        foreach ($accounts as $account) {
            $matchedType = null;
            foreach ($prefixToType as $prefix => $typeName) {
                if (str_starts_with($account->code, (string) $prefix)) {
                    $matchedType = $typeName;
                    break;
                }
            }

            if ($matchedType && isset($types[$matchedType])) {
                DB::table('acc_head')
                    ->where('id', $account->id)
                    ->update(['acc_type' => (string) $types[$matchedType]]);
            }
        }
    }
}


