<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
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
                'carry_over_limit_days' => 15,
            ],
            [
                'name' => 'إجازة مرضية',
                'code' => 'SL',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 90,
                'accrual_rate_per_month' => 1.5,
                'carry_over_limit_days' => 30,
            ],
            [
                'name' => 'إجازة عارضة',
                'code' => 'CL',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 7,
                'accrual_rate_per_month' => 0.5,
                'carry_over_limit_days' => 5,
            ],
            [
                'name' => 'إجازة بدون راتب',
                'code' => 'UL',
                'is_paid' => false,
                'requires_approval' => true,
                'max_per_request_days' => 365,
                'accrual_rate_per_month' => null,
                'carry_over_limit_days' => null,
            ],
            [
                'name' => 'إجازة أمومة',
                'code' => 'ML',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 120,
                'accrual_rate_per_month' => null,
                'carry_over_limit_days' => null,
            ],
            [
                'name' => 'إجازة أبوة',
                'code' => 'PL',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 14,
                'accrual_rate_per_month' => null,
                'carry_over_limit_days' => null,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
}
