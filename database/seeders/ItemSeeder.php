
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA'); // Arabic locale for realistic Arabic names

        $units = DB::table('units')->get();
        $prices = DB::table('prices')->get();

        if ($units->isEmpty() || $prices->isEmpty()) {
            $this->command->error("Please seed units and prices first.");
            return;
        }

        // Arabic product names and categories
        $arabicCategories = [
            'أجهزة إلكترونية', 'ملابس', 'أدوات منزلية', 'مواد غذائية', 'أدوية',
            'كتب ومكتبة', 'ألعاب', 'مستحضرات تجميل', 'أدوات رياضية', 'مفروشات'
        ];

        $arabicProducts = [
            'جهاز', 'قميص', 'طبق', 'حليب', 'دواء', 'كتاب', 'لعبة', 'كريم', 'كرة', 'وسادة',
            'هاتف', 'بنطلون', 'كوب', 'خبز', 'فيتامين', 'مجلة', 'دمية', 'شامبو', 'حذاء', 'بطانية',
            'كمبيوتر', 'جاكيت', 'ملعقة', 'جبن', 'مسكن', 'قلم', 'سيارة', 'صابون', 'حزام', 'مخدة',
            'تلفزيون', 'فستان', 'شوكة', 'لحم', 'مضاد', 'دفتر', 'قطار', 'عطر', 'ساعة', 'ستارة'
        ];

        $this->command->info('Starting to seed 1000 items...');

        for ($i = 1; $i <= 100; $i++) {
            // Generate realistic data
            $category = $faker->randomElement($arabicCategories);
            $product = $faker->randomElement($arabicProducts);
            $itemName = $product . ' ' . $category . ' ' . $i;

            $code = 10000 + $i;
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

            // For each unit, create item_units, barcodes, and prices
            foreach ($units as $index => $unit) {
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

                // Create item_units for this unit
                DB::table('item_units')->insert([
                    'item_id' => $itemId,
                    'unit_id' => $unit->id,
                    'u_val' => $unitValue,
                    'cost' => $unitCost,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create barcode for this unit
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

                // Create prices for this unit with each price list
                foreach ($prices as $price) {
                    DB::table('item_prices')->insert([
                        'item_id' => $itemId,
                        'price_id' => $price->id,
                        'unit_id' => $unit->id,
                        'price' => $unitPrice,
                        'discount' => $faker->randomFloat(2, 0, 25), // 0-25% discount
                        'tax_rate' => $faker->randomFloat(2, 0, 15), // 0-15% tax
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Progress indicator
            if ($i % 100 == 0) {
                $this->command->info("Seeded {$i} items...");
            }
        }

        $this->command->info('Successfully seeded 1000 items with units, barcodes, and prices!');
    }
}

