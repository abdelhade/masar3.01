<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SalaryCalculationService;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalaryCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SalaryCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SalaryCalculationService::class);
    }

    /** @test */
    public function it_calculates_hours_only_salary_correctly(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $shift = \App\Models\Shift::create([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'shift_type' => 'morning',
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);

        $employee = Employee::create([
            'name' => 'Test Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'ساعات عمل فقط',
            'shift_id' => $shift->id,
        ]);

        // Create attendance records
        $date = Carbon::parse('2025-01-01'); // Wednesday
        Attendance::create([
            'employee_id' => $employee->id,
            'employee_attendance_finger_print_id' => $employee->id,
            'employee_attendance_finger_print_name' => $employee->name,
            'date' => $date->format('Y-m-d'),
            'time' => '08:00:00',
            'type' => 'check_in',
            'status' => 'approved',
        ]);
        Attendance::create([
            'employee_id' => $employee->id,
            'employee_attendance_finger_print_id' => $employee->id,
            'employee_attendance_finger_print_name' => $employee->name,
            'date' => $date->format('Y-m-d'),
            'time' => '17:00:00',
            'type' => 'check_out',
            'status' => 'approved',
        ]);

        // Act
        $result = $this->service->calculateSalary(
            $employee,
            $date,
            $date
        );

        // Assert
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('salary_data', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertGreaterThan(0, $result['salary_data']['total_salary']);
    }

    /** @test */
    public function it_calculates_overtime_correctly(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $shift = \App\Models\Shift::create([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'ending_check_out' => '17:30:00',
            'shift_type' => 'morning',
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);

        $employee = Employee::create([
            'name' => 'Test Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'ساعات عمل و إضافي يومى',
            'shift_id' => $shift->id,
            'additional_hour_calculation' => 1.5,
        ]);

        // Create attendance with overtime
        $date = Carbon::parse('2025-01-01');
        Attendance::create([
            'employee_id' => $employee->id,
            'employee_attendance_finger_print_id' => $employee->id,
            'employee_attendance_finger_print_name' => $employee->name,
            'date' => $date->format('Y-m-d'),
            'time' => '08:00:00',
            'type' => 'check_in',
            'status' => 'approved',
        ]);
        Attendance::create([
            'employee_id' => $employee->id,
            'employee_attendance_finger_print_id' => $employee->id,
            'employee_attendance_finger_print_name' => $employee->name,
            'date' => $date->format('Y-m-d'),
            'time' => '19:00:00', // 2 hours overtime
            'type' => 'check_out',
            'status' => 'approved',
        ]);

        // Act
        $result = $this->service->calculateSalary(
            $employee,
            $date,
            $date
        );

        // Assert
        $this->assertGreaterThan(0, $result['summary']['overtime_minutes']);
        $this->assertGreaterThan(0, $result['salary_data']['overtime_salary']);
    }

    /** @test */
    public function it_handles_absent_days_correctly(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $shift = \App\Models\Shift::create([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'shift_type' => 'morning',
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);

        $employee = Employee::create([
            'name' => 'Test Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'ساعات عمل فقط',
            'shift_id' => $shift->id,
        ]);

        // No attendance records (absent)
        $startDate = Carbon::parse('2025-01-01'); // Wednesday
        $endDate = Carbon::parse('2025-01-03'); // Friday

        // Act
        $result = $this->service->calculateSalary(
            $employee,
            $startDate,
            $endDate
        );

        // Assert
        $this->assertGreaterThan(0, $result['summary']['absent_days']);
    }

    /** @test */
    public function it_calculates_attendance_only_salary_correctly(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $shift = \App\Models\Shift::create([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'beginning_check_in' => '07:00:00',
            'ending_check_in' => '09:00:00',
            'shift_type' => 'morning',
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);

        $employee = Employee::create([
            'name' => 'Test Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'حضور فقط',
            'shift_id' => $shift->id,
        ]);

        // Create check-in only (no check-out)
        $date = Carbon::parse('2025-01-01');
        Attendance::create([
            'employee_id' => $employee->id,
            'employee_attendance_finger_print_id' => $employee->id,
            'employee_attendance_finger_print_name' => $employee->name,
            'date' => $date->format('Y-m-d'),
            'time' => '08:00:00',
            'type' => 'check_in',
            'status' => 'approved',
        ]);

        // Act
        $result = $this->service->calculateSalary(
            $employee,
            $date,
            $date
        );

        // Assert
        $this->assertEquals(1, $result['summary']['present_days']);
        $this->assertGreaterThan(0, $result['salary_data']['total_salary']);
    }

    /** @test */
    public function it_handles_production_only_type(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $employee = Employee::create([
            'name' => 'Production Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'إنتاج فقط',
        ]);

        $date = Carbon::parse('2025-01-01');

        // Act
        $result = $this->service->calculateSalary(
            $employee,
            $date,
            $date
        );

        // Assert
        $this->assertEquals(0, $result['salary_data']['basic_salary']);
        $this->assertEquals(0, $result['salary_data']['total_salary']);
        $this->assertEquals('production_only', $result['salary_data']['calculation_type']);
    }
}

