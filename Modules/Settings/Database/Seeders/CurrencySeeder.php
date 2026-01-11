<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\Currency;
use Modules\Settings\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Currency (ID=1)
        $defaultCurrency = Currency::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Default Currency',
                'code' => 'EGP',
                'symbol' => 'EGP',
                'decimal_places' => 2,
                'is_default' => true,
                'is_active' => true,
                'rate_mode' => 'manual',
            ]
        );

        // Ensure it is marked as default even if it exists
        if (!$defaultCurrency->is_default) {
            $defaultCurrency->update(['is_default' => true]);
        }

        // 2. Create Initial Exchange Rate (1.0)
        ExchangeRate::firstOrCreate(
            [
                'currency_id' => $defaultCurrency->id,
                'rate_date' => now()->format('Y-m-d'),
            ],
            [
                'rate' => 1.00000000,
            ]
        );
    }
}
