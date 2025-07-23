<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ZakahTaxDeclarationSeeder extends Seeder
{
    public function run()
    {
        DB::table('zakah_tax_declarations')->insert([
            [
                'client_id' => 1, // تأكد من وجود عميل برقم 1 أو عدل حسب بياناتك
                'tax_file_number' => '1234567890',
                'declaration_type' => 'zakah',
                'period_from' => '2024-01-01',
                'period_to' => '2024-12-31',
                'amount' => 15000.00,
                'submission_date' => Carbon::now()->toDateString(),
                'status' => 'pending',
                'notes' => 'بيانات تجريبية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
} 