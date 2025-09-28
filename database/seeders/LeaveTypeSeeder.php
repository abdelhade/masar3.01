<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'إجازة سنوية',
                'code' => 'AL',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 30,
                'accrual_rate_per_month' => 2.5,
                'carry_over_limit_days' => 10,
            ],
            [
                'name' => 'إجازة مرضية',
                'code' => 'SL',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 15,
                'accrual_rate_per_month' => 1.25,
                'carry_over_limit_days' => 5,
            ],
            [
                'name' => 'إجازة عارضة',
                'code' => 'CL',
                'is_paid' => false,
                'requires_approval' => true,
                'max_per_request_days' => 3,
                'accrual_rate_per_month' => 0.5,
                'carry_over_limit_days' => 2,
            ],
            [
                'name' => 'إجازة أمومة',
                'code' => 'ML',
                'is_paid' => true,
                'requires_approval' => false,
                'max_per_request_days' => 90,
                'accrual_rate_per_month' => 0,
                'carry_over_limit_days' => 0,
            ],
            [
                'name' => 'إجازة أبوة',
                'code' => 'PL',
                'is_paid' => true,
                'requires_approval' => false,
                'max_per_request_days' => 7,
                'accrual_rate_per_month' => 0,
                'carry_over_limit_days' => 0,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}