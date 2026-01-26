<?php

declare(strict_types=1);

namespace Modules\HR\Resources\Views\Livewire\HrManagement\Attendances\Reports;

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Modules\HR\Models\AttendanceProcessingDetail;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Department;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $startDate;
    public string $endDate;
    public ?string $projectCode = '';
    public ?int $employeeId = null;
    public ?int $departmentId = null;

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function projects(): Collection
    {
        // Get unique project codes from attendance details
        $codes = AttendanceProcessingDetail::whereNotNull('project_code')
            ->distinct()
            ->pluck('project_code');

        // Get actual project names if available
        return Project::whereIn('project_code', $codes)
            ->orWhereIn('name', $codes)
            ->get(['id', 'name', 'project_code']);
    }

    #[Computed]
    public function employees(): Collection
    {
        return Employee::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function departments(): Collection
    {
        return Department::orderBy('title')->get(['id', 'title']);
    }

    #[Computed]
    public function allProjects()
    {
        return Project::all();
    }

    public function getProjectTitle($code)
    {
        if (!$code) return __('common.general');
        $project = $this->allProjects->where('project_code', $code)->first() 
                   ?? $this->allProjects->where('name', $code)->first();
        return $project ? $project->name : $code;
    }

    #[Computed]
    public function reportData()
    {
        $query = AttendanceProcessingDetail::with(['employee', 'department'])
            ->whereBetween('attendance_date', [$this->startDate, $this->endDate]);

        if ($this->projectCode) {
            $project = Project::where('project_code', $this->projectCode)
                ->orWhere('name', $this->projectCode)
                ->first();

            if ($project) {
                $query->where(function($q) use ($project) {
                    $q->where('project_code', $project->project_code)
                      ->orWhere('project_code', $project->name);
                });
            } else {
                $query->where('project_code', $this->projectCode);
            }
        }

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        return $query->orderBy('attendance_date', 'desc')->paginate(50);
    }

    #[Computed]
    public function summary()
    {
        $query = AttendanceProcessingDetail::whereBetween('attendance_date', [$this->startDate, $this->endDate]);

        if ($this->projectCode) {
            $project = Project::where('project_code', $this->projectCode)
                ->orWhere('name', $this->projectCode)
                ->first();

            if ($project) {
                $query->where(function($q) use ($project) {
                    $q->where('project_code', $project->project_code)
                      ->orWhere('project_code', $project->name);
                });
            } else {
                $query->where('project_code', $this->projectCode);
            }
        }

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        // Get basic stats directly from the processing details table
        $stats = (clone $query)
            ->selectRaw("
                SUM(attendance_actual_hours_count) as total_hours,
                SUM(attendance_overtime_minutes_count) as total_overtime_minutes,
                SUM(total_due_hourly_salary) as total_processed_cost
            ")->first();

        $totalCost = (float) ($stats->total_processed_cost ?? 0);
        $totalOvertimeMinutes = (float) ($stats->total_overtime_minutes ?? 0);
        
        // Calculate costs from actual data by processing each detail record
        // This ensures accuracy by using the same calculation method as when saving the data
        $details = (clone $query)
            ->with(['employee.shift'])
            ->get();
        
        $workHoursCost = 0;
        $overtimeCost = 0;
        
        foreach ($details as $detail) {
            $employee = $detail->employee;
            if (!$employee || $employee->salary_type === 'إنتاج فقط') {
                continue;
            }
            
            // Calculate hourly rate (same as in AttendanceProcessingService)
            $currentMonthDays = Carbon::parse($detail->attendance_date)->daysInMonth;
            $shiftHours = $employee->shift && $employee->shift->hours_per_day 
                ? $employee->shift->hours_per_day 
                : 8;
            $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
            
            // Handle overtime days differently (they are calculated as daily rate * multiplier)
            if ($detail->day_type === 'overtime_day') {
                // For overtime days, the total_due_hourly_salary already includes the overtime calculation
                // We can't easily split it, so we'll add it to work hours cost
                // This is a simplification, but matches the actual calculation in AttendanceProcessingService
                $workHoursCost += $detail->total_due_hourly_salary;
                continue;
            }
            
            // Calculate work hours cost for normal days
            $workHoursCost += $detail->attendance_actual_hours_count * $hourlyRate;
            
            // Calculate overtime cost
            if ($detail->attendance_overtime_minutes_count > 0) {
                $overtimeMultiplier = $employee->additional_hour_calculation ?? 1.5;
                $overtimeHours = $detail->attendance_overtime_minutes_count / 60;
                $overtimeCost += $overtimeHours * $hourlyRate * $overtimeMultiplier;
            }
        }
        
        // Round to 2 decimal places
        $workHoursCost = round($workHoursCost, 2);
        $overtimeCost = round($overtimeCost, 2);
        
        $basicCost = $workHoursCost;

        // Unified Project Counting
        $projectCodes = (clone $query)->whereNotNull('project_code')->distinct('project_code')->pluck('project_code');
        $uniqueProjectIds = $projectCodes->map(function($code) {
            $project = $this->allProjects->where('project_code', $code)->first() 
                       ?? $this->allProjects->where('name', $code)->first();
            return $project ? $project->id : $code;
        })->unique();

        return [
            'total_hours' => (float) ($stats->total_hours ?? 0),
            'total_overtime' => $totalOvertimeMinutes / 60,
            'active_projects' => $uniqueProjectIds->count(),
            'total_employees' => (int) $query->distinct('employee_id')->count('employee_id'),
            'work_hours_cost' => $basicCost,
            'overtime_cost' => $overtimeCost,
            'total_cost' => $totalCost,
        ];
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }
}; ?><div class="project-attendance-report">
    <!-- Filter Section -->
    <div class="card mb-4 shadow-sm border-top border-4 border-primary">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="las la-filter me-2 text-primary fs-4"></i>
                {{ __('hr.report_filters') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('common.from') }}</label>
                    <input type="date" wire:model.live="startDate" class="form-control" />
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('common.to') }}</label>
                    <input type="date" wire:model.live="endDate" class="form-control" />
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('hr.project') }}</label>
                    <select wire:model.live="projectCode" class="form-select">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($this->projects as $project)
                            <option value="{{ $project->project_code ?? $project->name }}">
                                {{ $project->name }} ({{ $project->project_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('hr.employee') }}</label>
                    <select wire:model.live="employeeId" class="form-select">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($this->employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">{{ __('hr.department') }}</label>
                    <select wire:model.live="departmentId" class="form-select">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($this->departments as $department)
                            <option value="{{ $department->id }}">{{ $department->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Row 1: Hours & Basic Stats -->
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-uppercase fw-bold m-0 opacity-75 small">{{ __('hr.total_work_hours') }}</h6>
                        <i class="las la-clock fs-2 opacity-25"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ number_format($this->summary['total_hours'], 2) }}</h4>
                    <div class="mt-2 pt-2 border-top border-white border-opacity-25">
                        <small class="opacity-75 d-block">{{ __('hr.work_hours_cost') }}</small>
                        <h5 class="fw-bold mb-0">{{ number_format($this->summary['work_hours_cost'], 2) }} <small class="fs-6 opacity-75">SAR</small></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-uppercase fw-bold m-0 opacity-75 small">{{ __('hr.total_overtime_hours') }}</h6>
                        <i class="las la-stopwatch fs-2 opacity-25"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ number_format($this->summary['total_overtime'], 2) }}</h4>
                    <div class="mt-2 pt-2 border-top border-white border-opacity-25">
                        <small class="opacity-75 d-block">{{ __('hr.overtime_cost') }}</small>
                        <h5 class="fw-bold mb-0">{{ number_format($this->summary['overtime_cost'], 2) }} <small class="fs-6 opacity-75">SAR</small></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-uppercase fw-bold m-0 opacity-75 small">{{ __('hr.active_projects_count') }}</h6>
                        <i class="las la-project-diagram fs-2 opacity-25"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $this->summary['active_projects'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm bg-warning text-white h-100 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-uppercase fw-bold m-0 opacity-75 small">{{ __('hr.employees') }}</h6>
                        <i class="las la-users fs-2 opacity-25"></i>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $this->summary['total_employees'] }}</h4>
                </div>
            </div>
        </div>

        <!-- Row 2: Total Cost -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-white h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-uppercase fw-bold m-0 opacity-75 small">{{ __('hr.total_cost') }}</h6>
                        <i class="las la-wallet fs-2 opacity-25"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($this->summary['total_cost'], 2) }} <small class="half-opacity fs-6">SAR</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="las la-table me-2 text-primary fs-4"></i>
                {{ __('hr.project_attendance_details') }}
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted fw-bold">
                        <tr>
                            <th class="px-4 py-3">{{ __('common.date') }}</th>
                            <th class="py-3">{{ __('hr.employee') }}</th>
                            <th class="py-3">{{ __('hr.department') }}</th>
                            <th class="py-3">{{ __('hr.project') }}</th>
                            <th class="py-3 text-center">{{ __('hr.work_hours') }}</th>
                            <th class="py-3 text-center">{{ __('hr.overtime_hours') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('hr.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->reportData as $detail)
                            <tr>
                                <td class="px-4 py-3 fw-medium">{{ $detail->attendance_date->format('Y-m-d') }}</td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            {{ mb_substr($detail->employee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $detail->employee->name }}</div>
                                            <small class="text-muted">#{{ $detail->employee->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">{{ $detail->department->title ?? '--' }}</td>
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $this->getProjectTitle($detail->project_code) }}</span>
                                        @if($detail->project_code && $detail->project_code !== $this->getProjectTitle($detail->project_code))
                                            <small class="text-muted opacity-75">({{ $detail->project_code }})</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 text-center fw-bold">{{ number_format((float) $detail->attendance_actual_hours_count, 2) }}</td>
                                <td class="py-3 text-center">
                                    @if($detail->attendance_overtime_minutes_count > 0)
                                        <span class="text-success fw-bold">
                                            +{{ number_format($detail->attendance_overtime_minutes_count / 60, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted opacity-50">--</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {!! $detail->status_badge !!}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="las la-folder-open fs-1 text-muted opacity-25 mb-3"></i>
                                        <p class="text-muted lead">{{ __('common.no_data_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($this->reportData->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $this->reportData->links() }}
            </div>
        @endif
    </div>
    <style>
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1) !important; }
        .project-attendance-report table thead th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .project-attendance-report .card { border-radius: 12px; }
        .project-attendance-report .form-select, .project-attendance-report .form-control { border-radius: 8px; padding: 0.6rem 1rem; border-color: #e9ecef; }
        .project-attendance-report .form-select:focus, .project-attendance-report .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05); }
        .project-attendance-report table tbody tr { transition: all 0.2s; }
        .project-attendance-report table tbody tr:hover { background-color: rgba(248, 249, 250, 0.8) !important; }
    </style>
</div>
