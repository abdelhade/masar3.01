<?php

namespace Modules\Services\Database\Factories;

use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceBookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceBooking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingDate = $this->faker->dateTimeBetween('now', '+30 days');
        
        // Use existing service or create one with proper branch_id
        $service = Service::inRandomOrder()->first();
        if (!$service) {
            $service = Service::factory()->create();
        }
        
        return [
            'service_id' => $service->id,
            'customer_id' => $this->faker->numberBetween(1, 5), // Use existing acc_head IDs
            'employee_id' => $this->faker->optional(0.8)->numberBetween(1, 5), // Use existing acc_head IDs
            'booking_date' => $bookingDate->format('Y-m-d'),
            'price' => $service->price,
            'notes' => $this->faker->optional(0.4)->paragraph(2),
            'customer_notes' => $this->faker->optional(0.3)->paragraph(1),
            'is_completed' => $this->faker->boolean(30), // 30% chance of being completed
            'is_cancelled' => $this->faker->boolean(5), // 5% chance of being cancelled
            'cancelled_at' => $this->faker->optional(0.05)->dateTimeBetween('-30 days', 'now'),
            'cancellation_reason' => $this->faker->optional(0.05)->sentence(),
            'branch_id' => $service->branch_id ?? 1, // Use service's branch_id or default to 1
            'created_by' => 1, // Use existing user ID
            'updated_by' => $this->faker->optional(0.3)->passthrough(1), // Use existing user ID or null
        ];
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'is_cancelled' => false,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'is_cancelled' => false,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'is_cancelled' => true,
            'cancelled_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'cancellation_reason' => $this->faker->randomElement([
                'إلغاء من العميل',
                'عدم توفر الموظف',
                'مشكلة تقنية',
                'تغيير في المواعيد',
                'أسباب شخصية'
            ]),
        ]);
    }

    /**
     * Create a booking for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Create a booking for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_date' => now()->addDay()->format('Y-m-d'),
        ]);
    }

    /**
     * Create a booking for next week.
     */
    public function nextWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_date' => now()->addWeek()->format('Y-m-d'),
        ]);
    }

    /**
     * Create a booking with specific service.
     */
    public function withService(Service $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $service->id,
            'price' => $service->price,
        ]);
    }

    /**
     * Create a booking with specific customer.
     */
    public function withCustomer(int $customerId): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customerId,
        ]);
    }

    /**
     * Create a booking with specific employee.
     */
    public function withEmployee(int $employeeId): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => $employeeId,
        ]);
    }

    /**
     * Create a booking with specific branch.
     */
    public function withBranch(int $branchId): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branchId,
        ]);
    }
}