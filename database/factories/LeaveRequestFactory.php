<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(1, 14));
        $durationDays = Carbon::parse($startDate)->diffInDays($endDate) + 1;

        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'status' => $this->faker->randomElement(['draft', 'submitted', 'approved', 'rejected', 'cancelled']),
            'approver_id' => $this->faker->optional(0.7)->randomElement(User::pluck('id')->toArray()),
            'approved_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 month', 'now'),
            'reason' => $this->faker->optional(0.8)->sentence(),
            'overlaps_attendance' => $this->faker->boolean(20),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approver_id' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approver_id' => User::factory(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
