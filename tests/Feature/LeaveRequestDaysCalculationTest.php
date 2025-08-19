<?php

declare(strict_types=1);

use App\Livewire\Leaves\LeaveRequests\Create;
use App\Models\Employee;
use App\Models\LeaveType;
use Livewire\Livewire;

test('leave request calculates days correctly', function () {
    $employee = Employee::factory()->create();
    $leaveType = LeaveType::factory()->create();

    Livewire::test(Create::class)
        ->set('employee_id', $employee->id)
        ->set('leave_type_id', $leaveType->id)
        ->set('start_date', '2024-01-01')
        ->set('end_date', '2024-01-05')
        ->assertSet('calculated_days', 5);
});

test('leave request calculates single day correctly', function () {
    $employee = Employee::factory()->create();
    $leaveType = LeaveType::factory()->create();

    Livewire::test(Create::class)
        ->set('employee_id', $employee->id)
        ->set('leave_type_id', $leaveType->id)
        ->set('start_date', '2024-01-01')
        ->set('end_date', '2024-01-01')
        ->assertSet('calculated_days', 1);
});
