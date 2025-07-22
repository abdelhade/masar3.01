<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'int1' => $this->faker->numberBetween(1, 10000),
            'int2' => $this->faker->numberBetween(1, 10000),
            'var1' => $this->faker->word,
            'var2' => $this->faker->word,
        ];
    }
}
