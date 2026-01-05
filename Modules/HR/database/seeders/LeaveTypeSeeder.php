<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Modules\HR\Models\LeaveType;
use Illuminate\Database\Seeder;

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
            ],
            [
                'name' => 'إجازة مرضية',
                'code' => 'SL',
                'is_paid' => true,
                'requires_approval' => true,
                'max_per_request_days' => 15,
            ],
            [
                'name' => 'إجازة عارضة',
                'code' => 'CL',
                'is_paid' => false,
                'requires_approval' => true,
                'max_per_request_days' => 3,
            ],
            [
                'name' => 'إجازة أمومة',
                'code' => 'ML',
                'is_paid' => true,
                'requires_approval' => false,
                'max_per_request_days' => 90,
            ],
            [
                'name' => 'إجازة أبوة',
                'code' => 'PL',
                'is_paid' => true,
                'requires_approval' => false,
                'max_per_request_days' => 7,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }
    }
}
