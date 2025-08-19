<?php

namespace Tests\Feature;

use App\LeaveBalanceService;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_leave_type(): void
    {
        $leaveType = LeaveType::factory()->create([
            'name' => 'إجازة سنوية',
            'code' => 'AL',
            'is_paid' => true,
        ]);

        $this->assertDatabaseHas('leave_types', [
            'name' => 'إجازة سنوية',
            'code' => 'AL',
            'is_paid' => true,
        ]);
    }

    public function test_can_create_employee_leave_balance(): void
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        $balance = EmployeeLeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2024,
            'opening_balance_days' => 30,
        ]);

        $this->assertDatabaseHas('employee_leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2024,
            'opening_balance_days' => 30,
        ]);

        $this->assertEquals(30, $balance->remaining_days);
    }

    public function test_can_create_leave_request(): void
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        $request = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'draft',
        ]);
    }

    public function test_leave_balance_service_reserve_pending(): void
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        // إنشاء رصيد أولي
        $balance = EmployeeLeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2024,
            'opening_balance_days' => 30,
            'pending_days' => 0,
        ]);

        $service = new LeaveBalanceService;
        $result = $service->reservePending($employee->id, $leaveType->id, 2024, 5);

        $this->assertTrue($result);

        $balance->refresh();
        $this->assertEquals(5, $balance->pending_days);
        $this->assertEquals(25, $balance->remaining_days);
    }

    public function test_leave_balance_service_consume_approved(): void
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        // إنشاء رصيد مع أيام معلقة
        $balance = EmployeeLeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2024,
            'opening_balance_days' => 30,
            'pending_days' => 5,
            'used_days' => 0,
        ]);

        $service = new LeaveBalanceService;
        $service->consumeApproved($employee->id, $leaveType->id, 2024, 5);

        $balance->refresh();
        $this->assertEquals(0, $balance->pending_days);
        $this->assertEquals(5, $balance->used_days);
        $this->assertEquals(25, $balance->remaining_days);
    }

    public function test_leave_balance_service_release_pending(): void
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        // إنشاء رصيد مع أيام معلقة
        $balance = EmployeeLeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2024,
            'opening_balance_days' => 30,
            'pending_days' => 5,
        ]);

        $service = new LeaveBalanceService;
        $service->releasePending($employee->id, $leaveType->id, 2024, 5);

        $balance->refresh();
        $this->assertEquals(0, $balance->pending_days);
        $this->assertEquals(30, $balance->remaining_days);
    }

    public function test_cannot_reserve_more_than_available_balance(): void
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::factory()->create();

        // إنشاء رصيد محدود
        EmployeeLeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => 2024,
            'opening_balance_days' => 10,
        ]);

        $service = new LeaveBalanceService;
        $result = $service->reservePending($employee->id, $leaveType->id, 2024, 15);

        $this->assertFalse($result);
    }

    public function test_leave_request_calculates_duration_correctly(): void
    {
        $request = LeaveRequest::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-05',
        ]);

        $this->assertEquals(5, $request->duration_days);
    }

    public function test_leave_request_status_transitions(): void
    {
        $request = LeaveRequest::factory()->draft()->create();

        $this->assertTrue($request->isDraft());
        $this->assertTrue($request->canBeCancelled());

        $request->update(['status' => 'submitted']);
        $this->assertTrue($request->isSubmitted());
        $this->assertTrue($request->canBeApproved());
        $this->assertTrue($request->canBeRejected());

        $request->update(['status' => 'approved']);
        $this->assertTrue($request->isApproved());
        $this->assertFalse($request->canBeApproved());
        $this->assertFalse($request->canBeRejected());
    }
}
