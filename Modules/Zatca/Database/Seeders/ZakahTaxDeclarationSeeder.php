<?php

declare(strict_types=1);

namespace Modules\Zatca\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ZakahTaxDeclarationSeeder extends Seeder
{
    public function run(): void
    {
        // التحقق من وجود الجدول أولاً
        if (! \Schema::hasTable('zakah_tax_declarations')) {
            return;
        }

        // التحقق من وجود عميل برقم 1
        $clientExists = DB::table('clients')->where('id', 1)->exists();

        if (! $clientExists) {
            return;
        }

        DB::table('zakah_tax_declarations')->updateOrInsert(
            [
                'client_id' => 1,
                'tax_file_number' => '1234567890',
                'declaration_type' => 'zakah',
                'period_from' => '2024-01-01',
                'period_to' => '2024-12-31',
            ],
            [
                'amount' => 15000.00,
                'submission_date' => Carbon::now()->toDateString(),
                'status' => 'pending',
                'notes' => 'بيانات تجريبية',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
