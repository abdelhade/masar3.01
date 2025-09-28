<?php

namespace Modules\Services\Database\Factories;

use Modules\Services\Models\ServiceUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitNames = [
            'ساعة',
            'جلسة',
            'خدمة',
            'مشروع',
            'صفحة',
            'ميجابايت',
            'جهاز',
            'شخص',
            'يوم',
            'أسبوع'
        ];

        $cost = $this->faker->randomFloat(3, 10, 500);
        $sellPrice = $cost + $this->faker->randomFloat(3, 5, 200);

        return [
            'code' => $this->faker->unique()->numberBetween(2000, 9999),
            'name' => $this->faker->randomElement($unitNames),
            'cost' => $cost,
            'sell_price' => $sellPrice,
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Create an active service unit.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive service unit.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create an hour unit.
     */
    public function hour(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'ساعة',
            'code' => 2001,
            'cost' => $this->faker->randomFloat(3, 50, 200),
            'sell_price' => $this->faker->randomFloat(3, 100, 300),
        ]);
    }

    /**
     * Create a session unit.
     */
    public function session(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'جلسة',
            'code' => 2002,
            'cost' => $this->faker->randomFloat(3, 30, 150),
            'sell_price' => $this->faker->randomFloat(3, 80, 250),
        ]);
    }

    /**
     * Create a service unit.
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'خدمة',
            'code' => 2003,
            'cost' => $this->faker->randomFloat(3, 100, 500),
            'sell_price' => $this->faker->randomFloat(3, 200, 800),
        ]);
    }

    /**
     * Create a project unit.
     */
    public function project(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'مشروع',
            'code' => 2004,
            'cost' => $this->faker->randomFloat(3, 500, 2000),
            'sell_price' => $this->faker->randomFloat(3, 1000, 5000),
        ]);
    }
}
