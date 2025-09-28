<?php

namespace Modules\Services\Database\Factories;

use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceType;
use Modules\Services\Models\ServiceUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceType = ServiceType::factory()->create();
        $serviceUnit = ServiceUnit::factory()->create();
        
        $name = $this->faker->randomElement([
            'خدمة استشارة تقنية',
            'خدمة صيانة الأجهزة',
            'خدمة إصلاح الكمبيوتر',
            'خدمة تركيب البرامج',
            'خدمة تدريب المستخدمين',
            'خدمة دعم فني',
            'خدمة تنظيف البيانات',
            'خدمة النسخ الاحتياطي',
            'خدمة الأمان السيبراني',
            'خدمة تطوير المواقع'
        ]);

        return [
            'name' => $name,
            'code' => 'SRV-' . $this->faker->unique()->numerify('####'),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->randomFloat(2, 50, 2000),
            'cost' => $this->faker->randomFloat(2, 20, 1000),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
            'is_taxable' => $this->faker->boolean(70), // 70% chance of being taxable
            'service_type_id' => $serviceType->id,
            'service_unit_id' => $serviceUnit->id,
            'branch_id' => 1, // Use the default branch ID
        ];
    }

    /**
     * Indicate that the service is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the service is taxable.
     */
    public function taxable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_taxable' => true,
        ]);
    }

    /**
     * Indicate that the service is non-taxable.
     */
    public function nonTaxable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_taxable' => false,
        ]);
    }

    /**
     * Create a consultation service.
     */
    public function consultation(): static
    {
        return $this->state(function (array $attributes) {
            $serviceType = ServiceType::factory()->create(['name' => 'استشارة']);
            return [
                'name' => 'خدمة استشارة تقنية',
                'service_type_id' => $serviceType->id,
                'price' => $this->faker->randomFloat(2, 100, 500),
            ];
        });
    }

    /**
     * Create a maintenance service.
     */
    public function maintenance(): static
    {
        return $this->state(function (array $attributes) {
            $serviceType = ServiceType::factory()->create(['name' => 'صيانة']);
            return [
                'name' => 'خدمة صيانة الأجهزة',
                'service_type_id' => $serviceType->id,
                'price' => $this->faker->randomFloat(2, 200, 800),
            ];
        });
    }

    /**
     * Create a repair service.
     */
    public function repair(): static
    {
        return $this->state(function (array $attributes) {
            $serviceType = ServiceType::factory()->create(['name' => 'إصلاح']);
            return [
                'name' => 'خدمة إصلاح الأجهزة',
                'service_type_id' => $serviceType->id,
                'price' => $this->faker->randomFloat(2, 300, 1200),
            ];
        });
    }

    /**
     * Create a service with specific service type.
     */
    public function withServiceType(ServiceType $serviceType): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type_id' => $serviceType->id,
        ]);
    }

    /**
     * Create a service with specific service unit.
     */
    public function withServiceUnit(ServiceUnit $serviceUnit): static
    {
        return $this->state(fn (array $attributes) => [
            'service_unit_id' => $serviceUnit->id,
        ]);
    }
}