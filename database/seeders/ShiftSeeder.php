<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'days' => json_encode(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
            'shift_type' => 'morning',
            'notes' => 'Shift 1',
        ];

        // Add optional columns only if they exist in the table
        if (Schema::hasColumn('shifts', 'beginning_check_in')) {
            $data['beginning_check_in'] = '07:30:00';
        }

        if (Schema::hasColumn('shifts', 'ending_check_in')) {
            $data['ending_check_in'] = '08:30:00';
        }

        if (Schema::hasColumn('shifts', 'allowed_late_minutes')) {
            $data['allowed_late_minutes'] = 15;
        }

        if (Schema::hasColumn('shifts', 'beginning_check_out')) {
            $data['beginning_check_out'] = '15:30:00';
        }

        if (Schema::hasColumn('shifts', 'ending_check_out')) {
            $data['ending_check_out'] = '16:30:00';
        }

        if (Schema::hasColumn('shifts', 'allowed_early_leave_minutes')) {
            $data['allowed_early_leave_minutes'] = 15;
        }

        Shift::firstOrCreate(
            ['name' => 'general'],
            $data
        );
    }
}
