<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Varibal;
use App\Models\VaribalValue;

class VaribalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Size varibal
        $sizeVaribal = Varibal::create([
            'name' => 'المقاس',
            'description' => 'مقاسات المنتج المختلفة'
        ]);

        // Add size values
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];

        foreach ($sizes as $size) {
            VaribalValue::create([
                'varibal_id' => $sizeVaribal->id,
                'value' => $size
            ]);
        }

        // Create Color varibal
        $colorVaribal = Varibal::create([
            'name' => 'اللون',
            'description' => 'ألوان المنتج المختلفة'
        ]);

        // Add color values
        $colors = ['أبيض', 'أسود', 'أحمر', 'أزرق', 'أخضر'];

        foreach ($colors as $color) {
            VaribalValue::create([
                'varibal_id' => $colorVaribal->id,
                'value' => $color
            ]);
        }
    }
}
