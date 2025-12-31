<?php

namespace Modules\Checks\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckPortfoliosAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تحديث الحسابات الأب
        DB::table('acc_head')->whereIn('id', [27, 43])->update(['acc_type' => 17]);

        // تحديث حافظات الأوراق المالية
        DB::table('acc_head')->whereIn('id', [63, 66])->update(['acc_type' => 17]);
    }
}
