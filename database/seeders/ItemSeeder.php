<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA'); // Arabic locale for realistic Arabic names

        $units = DB::table('units')->get();
        $prices = DB::table('prices')->get();

        if ($units->isEmpty() || $prices->isEmpty()) {
            $this->command->error('Please seed units and prices first.');

            return;
        }

        // Arabic product names and categories
        $arabicCategories = [
            'أجهزة إلكترونية', 'ملابس', 'أدوات منزلية', 'مواد غذائية', 'أدوية',
            'كتب ومكتبة', 'ألعاب', 'مستحضرات تجميل', 'أدوات رياضية', 'مفروشات',
        ];

        $arabicProducts = [
            'جهاز', 'قميص', 'طبق', 'حليب', 'دواء', 'كتاب', 'لعبة', 'كريم', 'كرة', 'وسادة',
            'هاتف', 'بنطلون', 'كوب', 'خبز', 'فيتامين', 'مجلة', 'دمية', 'شامبو', 'حذاء', 'بطانية',
            'كمبيوتر', 'جاكيت', 'ملعقة', 'جبن', 'مسكن', 'قلم', 'سيارة', 'صابون', 'حزام', 'مخدة',
            'تلفزيون', 'فستان', 'شوكة', 'لحم', 'مضاد', 'دفتر', 'قطار', 'عطر', 'ساعة', 'ستارة',
        ];

        $totalItems = 20000;
        $this->command->info("Starting to seed {$totalItems} items...");
        $this->command->info('This may take several minutes...');

        // Get first unit only (each item will have only one unit)
        $unit = $units->first();
        // Get first price list only
        $price = $prices->first();

        for ($i = 1; $i <= $totalItems; $i++) {
            // Generate realistic data with unique name
            $category = $faker->randomElement($arabicCategories);
            $product = $faker->randomElement($arabicProducts);
            $itemName = $product.' '.$category.' '.$i.' '.$faker->unique()->numberBetween(100000, 999999);

            // Get the last code to continue from where we left off
            $lastCode = DB::table('items')->max('code') ?? 10000;
            $code = $lastCode + $i;
            $baseCost = $faker->randomFloat(2, 5, 500);

            $itemId = DB::table('items')->insertGetId([
                'name' => $itemName,
                'code' => $code,
                'info' => $faker->sentence(3),
                'tenant' => 1,
                'branch_id' => null, // Global items, not branch-specific
                'average_cost' => $baseCost,
                'min_order_quantity' => $faker->numberBetween(1, 10),
                'max_order_quantity' => $faker->numberBetween(100, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Calculate unit-specific cost and price
            $unitCost = $baseCost * $faker->randomFloat(2, 0.5, 2.0); // 50%-200% of base cost
            $unitPrice = $unitCost * $faker->randomFloat(2, 1.2, 2.5); // 20-150% markup

            // Set unit value based on unit type
            if ($unit->name === 'قطعه') {
                $unitValue = 1; // قطعه always has u_val = 1
            } elseif ($unit->name === 'كرتونة') {
                $unitValue = $faker->numberBetween(2, 50); // كرتونة has u_val > 1
            } else {
                $unitValue = $faker->numberBetween(1, 50); // Other units get random values
            }

            // Create single item_unit for this item (one row only)
            DB::table('item_units')->insert([
                'item_id' => $itemId,
                'unit_id' => $unit->id,
                'u_val' => $unitValue,
                'cost' => $unitCost,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create single barcode for this item
            DB::table('barcodes')->insert([
                'item_id' => $itemId,
                'unit_id' => $unit->id,
                'barcode' => $faker->unique()->ean13(),
                'isdeleted' => 0,
                'tenant' => 1,
                'branch_id' => null, // Global barcodes, not branch-specific
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create price with discount (discount in second row/item_prices)
            $discount = $faker->randomFloat(2, 0, 25); // 0-25% discount
            DB::table('item_prices')->insert([
                'item_id' => $itemId,
                'price_id' => $price->id,
                'unit_id' => $unit->id,
                'price' => $unitPrice,
                'discount' => $discount, // Discount in separate row
                'tax_rate' => $faker->randomFloat(2, 0, 15), // 0-15% tax
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Progress indicator
            if ($i % 500 == 0) {
                $this->command->info("Seeded {$i}/{$totalItems} items...");
            }
        }

        $this->command->info("Successfully seeded {$totalItems} items with units, barcodes, and prices!");
    }
}
