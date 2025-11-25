<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AttendanceProcessingService;
use App\Models\Employee;
use App\Models\AttendanceProcessing;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceProcessingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AttendanceProcessingService::class);
    }

    /** @test */
    public function it_prevents_overlapping_processing_for_same_employee(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $employee = Employee::create([
            'name' => 'Test Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'ساعات عمل فقط',
        ]);
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        // Create existing processing
        AttendanceProcessing::create([
            'employee_id' => $employee->id,
            'department_id' => $department->id,
            'period_start' => '2025-01-15',
            'period_end' => '2025-02-15',
            'type' => 'single',
            'total_days' => 31,
            'working_days' => 22,
            'total_hours' => 176,
            'status' => 'pending',
        ]);

        // Act
        $result = $this->service->processSingleEmployee(
            $employee,
            $startDate,
            $endDate
        );

        // Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('تكرار في المعالجة', $result['error']);
    }

    /** @test */
    public function it_rejects_processing_for_inactive_employee(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $employee = Employee::create([
            'name' => 'Inactive Employee',
            'status' => 'معطل',
            'department_id' => $department->id,
            'salary' => 10000,
        ]);
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        // Act
        $result = $this->service->processSingleEmployee(
            $employee,
            $startDate,
            $endDate
        );

        // Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('معطل', $result['error']);
    }

    /** @test */
    public function it_processes_single_employee_successfully(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $employee = Employee::create([
            'name' => 'Active Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'ساعات عمل فقط',
        ]);
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        // Act
        $result = $this->service->processSingleEmployee(
            $employee,
            $startDate,
            $endDate
        );

        // Assert
        $this->assertArrayNotHasKey('error', $result);
        $this->assertArrayHasKey('processing_id', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('salary_data', $result);
    }

    /** @test */
    public function it_processes_multiple_employees(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $employees = [];
        for ($i = 1; $i <= 3; $i++) {
            $employees[] = Employee::create([
                'name' => "Employee {$i}",
                'status' => 'مفعل',
                'department_id' => $department->id,
                'salary' => 10000,
                'salary_type' => 'ساعات عمل فقط',
            ]);
        }
        $employeeIds = array_column($employees, 'id');
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        // Act
        $results = $this->service->processMultipleEmployees(
            $employeeIds,
            $startDate,
            $endDate
        );

        // Assert
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertArrayNotHasKey('error', $result);
            $this->assertArrayHasKey('processing_id', $result);
        }
    }

    /** @test */
    public function it_handles_partial_overlaps_correctly(): void
    {
        // Arrange
        $department = \App\Models\Department::firstOrCreate(['title' => 'Test Department']);
        $employee = Employee::create([
            'name' => 'Test Employee',
            'status' => 'مفعل',
            'department_id' => $department->id,
            'salary' => 10000,
            'salary_type' => 'ساعات عمل فقط',
        ]);
        
        // Create processing for Jan 15 - Feb 15
        AttendanceProcessing::create([
            'employee_id' => $employee->id,
            'department_id' => $department->id,
            'period_start' => '2025-01-15',
            'period_end' => '2025-02-15',
            'type' => 'single',
            'total_days' => 32,
            'working_days' => 22,
            'total_hours' => 176,
            'status' => 'pending',
        ]);

        // Try to process Jan 1 - Jan 20 (overlaps with existing)
        $result = $this->service->processSingleEmployee(
            $employee,
            Carbon::parse('2025-01-01'),
            Carbon::parse('2025-01-20')
        );

        // Assert
        $this->assertArrayHasKey('error', $result);
    }
}

