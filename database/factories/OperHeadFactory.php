<?php

namespace Database\Factories;

use App\Models\OperHead;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperHeadFactory extends Factory
{
    protected $model = OperHead::class;

    public function definition(): array
    {
        return [
            'pro_id'        => $this->faker->numberBetween(1, 10000),
            'pro_type' => 59, // Default to manufacturing invoice type
            'branch_id'     => 1, // Default branch ID
            'is_stock'      => $this->faker->boolean,
            'is_finance'    => $this->faker->boolean,
            'is_manager'    => $this->faker->boolean,
            'is_journal'    => $this->faker->boolean,
            'journal_type'  => $this->faker->numberBetween(1, 5),
            // 'currency_id'   => $this->faker->numberBetween(1, 5),
            'currency_rate' => $this->faker->randomFloat(6, 0.5, 50),
            'info'          => $this->faker->sentence(5),
            'pro_date'      => $this->faker->date(),
            'pro_num'       => $this->faker->uuid,
            'pro_serial'    => $this->faker->uuid,
            'tax_num'       => $this->faker->numerify('TAX###'),
            // 'store_id'      => $this->faker->numberBetween(1, 100),
            // 'emp_id'        => $this->faker->numberBetween(1, 500),
            'pro_value'     => $this->faker->randomFloat(2, 100, 100000),
            'fat_net'       => $this->faker->randomFloat(2, 100, 200000),
            'user'          => 1,
            'tenant'        => 1,
            // 'branch'        => 1, // Removed: column doesn't exist (removed in migration 2025_09_14_183413)
            'status'        => 1,
            'paid_from_client' => $this->faker->randomFloat(2, 0, 50000),
        ];
    }
}
