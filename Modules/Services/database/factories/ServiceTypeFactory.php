<?php

namespace Modules\Services\Database\Factories;

use Modules\Services\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceTypeNames = [
            'استشارة',
            'صيانة',
            'إصلاح',
            'تركيب',
            'تدريب',
            'دعم فني',
            'تطوير',
            'أمان',
            'نسخ احتياطي',
            'تنظيف'
        ];

        return [
            'code' => $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->randomElement($serviceTypeNames),
            'branch_id' => 1, // Use the default branch ID
        ];
    }

    /**
     * Create a consultation service type.
     */
    public function consultation(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'استشارة',
            'code' => 1001,
        ]);
    }

    /**
     * Create a maintenance service type.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'صيانة',
            'code' => 1002,
        ]);
    }

    /**
     * Create a repair service type.
     */
    public function repair(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'إصلاح',
            'code' => 1003,
        ]);
    }

    /**
     * Create an installation service type.
     */
    public function installation(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'تركيب',
            'code' => 1004,
        ]);
    }

    /**
     * Create a training service type.
     */
    public function training(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'تدريب',
            'code' => 1005,
        ]);
    }

    /**
     * Create a service type with specific branch.
     */
    public function withBranch(int $branchId): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branchId,
        ]);
    }
}
