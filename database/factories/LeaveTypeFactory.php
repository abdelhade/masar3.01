<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'إجازة سنوية',
                'إجازة مرضية',
                'إجازة عارضة',
                'إجازة بدون راتب',
                'إجازة أمومة',
                'إجازة أبوة',
                'إجازة تعليمية',
                'إجازة حج',
                'إجازة عيد',
            ]),
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'is_paid' => $this->faker->boolean(80),
            'requires_approval' => $this->faker->boolean(90),
            'max_per_request_days' => $this->faker->randomElement([7, 14, 30, 60, 90, 120]),
        ];
    }
}
