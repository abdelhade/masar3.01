<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'code' => $this->faker->unique()->numberBetween(1000000000, 9999999999),
            'info' => $this->faker->optional()->sentence,
            'average_cost' => $this->faker->randomFloat(2, 10, 500),
            'min_order_quantity' => $this->faker->numberBetween(1, 5),
            'max_order_quantity' => $this->faker->numberBetween(6, 20),
            'branch_id' => null, // Allow null for testing, or set to 1 if default branch exists
            'tenant' => 0,
            'is_active' => 1,
            'isdeleted' => 0,
            'type' => 1,
        ];
    }
}
