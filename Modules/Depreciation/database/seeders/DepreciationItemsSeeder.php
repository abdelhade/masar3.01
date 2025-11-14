<?php

namespace Modules\Depreciation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Depreciation\Models\DepreciationItem;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;

class DepreciationItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Get first available branch
        $branch = Branch::first();
        if (!$branch) {
            $this->command->error('No branches found. Please create a branch first.');
            return;
        }

        // Get some asset accounts (acc_type = 11)
        $assetAccounts = AccHead::where('acc_type', 13)
            ->where('isdeleted', 0)
            ->take(3)
            ->get();

        if ($assetAccounts->isEmpty()) {
            $this->command->info('No asset accounts found. Creating sample depreciation items without asset accounts.');
            $assetAccounts = collect([null, null, null]);
        }

        $sampleItems = [
            [
                'name' => 'جهاز كمبيوتر محمول - HP',
                'purchase_date' => '2023-01-15',
                'cost' => 5000.00,
                'useful_life' => 4,
                'salvage_value' => 500.00,
                'depreciation_method' => 'straight_line',
                'notes' => 'جهاز كمبيوتر محمول للمحاسبة',
            ],
            [
                'name' => 'مكتب خشبي تنفيذي',
                'purchase_date' => '2022-06-20',
                'cost' => 2500.00,
                'useful_life' => 10,
                'salvage_value' => 250.00,
                'depreciation_method' => 'straight_line',
                'notes' => 'مكتب تنفيذي لمدير القسم',
            ],
            [
                'name' => 'سيارة نقل - تويوتا',
                'purchase_date' => '2021-03-10',
                'cost' => 80000.00,
                'useful_life' => 8,
                'salvage_value' => 10000.00,
                'depreciation_method' => 'straight_line',
                'notes' => 'سيارة نقل البضائع',
            ],
        ];

        foreach ($sampleItems as $index => $itemData) {
            $assetAccount = $assetAccounts->get($index);
            
            // Calculate annual depreciation
            $annualDepreciation = ($itemData['cost'] - $itemData['salvage_value']) / $itemData['useful_life'];
            
            // Calculate accumulated depreciation based on years passed
            $yearsUsed = now()->diffInYears($itemData['purchase_date']);
            $accumulatedDepreciation = min($yearsUsed * $annualDepreciation, $itemData['cost'] - $itemData['salvage_value']);

            DepreciationItem::create([
                'name' => $itemData['name'],
                'purchase_date' => $itemData['purchase_date'],
                'cost' => $itemData['cost'],
                'useful_life' => $itemData['useful_life'],
                'salvage_value' => $itemData['salvage_value'],
                'annual_depreciation' => $annualDepreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'depreciation_method' => $itemData['depreciation_method'],
                'asset_account_id' => $assetAccount?->id,
                'branch_id' => $branch->id,
                'notes' => $itemData['notes'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Sample depreciation items created successfully!');
    }
}