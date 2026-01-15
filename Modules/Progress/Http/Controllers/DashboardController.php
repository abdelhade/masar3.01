<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Progress\Models\ProjectProgress;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\WorkItem;
use Modules\HR\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // --- Filters ---
        $status = $request->input('status');
        $employeeId = $request->input('employee_id');
        $projectTypeId = $request->input('project_type_id');
        $projectId = $request->input('project_id');
        $itemId = $request->input('item_id');
        $dateRange = $request->input('date_range'); // "Last X Days"

        // Base Query for Projects
        $projectsQuery = ProjectProgress::query();

        if ($status && $status !== 'All') {
            $projectsQuery->where('status', $status);
        }
        if ($projectTypeId) {
            $projectsQuery->where('project_type_id', $projectTypeId);
        }
        if ($projectId) {
            $projectsQuery->where('id', $projectId);
        }
        // Filter by Employee (via relationship)
        if ($employeeId) {
            $projectsQuery->whereHas('employees', function ($q) use ($employeeId) {
                $q->where('employees.id', $employeeId);
            });
        }
        // Filter by Item (via relationship)
        if ($itemId) {
            $projectsQuery->whereHas('items', function ($q) use ($itemId) {
                $q->where('work_item_id', $itemId);
            });
        }
        // Filter by Date Range (Recent Activity or Start Date? Assuming Start Date or Updated At)
        if ($dateRange) {
            $date = Carbon::now()->subDays($dateRange);
            $projectsQuery->where('updated_at', '>=', $date);
        }

        $projects = $projectsQuery->with(['items'])->paginate(10);
        $allProjects = $projectsQuery->get(); // For Charts calculations (unpaginated)

        // --- Statistics Cards ---
        $totalEmployees = Employee::count(); // Total distinct employees in system (or filtered? usually system total)
        // If "Employee" filter is active, maybe show count of projects for that employee?
        // But prompt says "Total Employees" as a stat card, usually global or filtered context.
        // Let's keep it global for now unless filter is strictly applied.
        $totalProjects = ProjectProgress::count(); 

        // Overall Completion (Weighted by quantity)
        $totalQty = 0;
        $totalCompleted = 0;
        
        // --- Chart Data Calculation ---
        $plannedData = 0;
        $actualData = 0;

        $projectNames = [];
        $projectProgressValues = [];

        $statusCounts = [
            'active' => 0,
            'completed' => 0,
            'pending' => 0,
        ];

        foreach ($allProjects as $project) {
            // Status Distribution
            $st = strtolower($project->status);
            if (isset($statusCounts[$st])) {
                $statusCounts[$st]++;
            } else {
                // simple fallback
                if (!isset($statusCounts['other'])) $statusCounts['other'] = 0;
                $statusCounts['other']++;
            }

            // Project Progress Chart
            $projTotal = 0;
            $projCompleted = 0;
            $projPlanned = 0;

            foreach ($project->items as $item) {
                $projTotal += $item->total_quantity;
                $projCompleted += $item->completed_quantity;

                // Planned Calculation:
                // If today is past end_date, planned = total.
                // If today is before start_date, planned = 0.
                // If in between, planned = days_passed * daily_qty.
                $start = $item->start_date ? Carbon::parse($item->start_date) : null;
                $end = $item->end_date ? Carbon::parse($item->end_date) : null;
                
                if ($start) {
                    if (now()->lt($start)) {
                        $pQty = 0;
                    } elseif ($end && now()->gt($end)) {
                        $pQty = $item->total_quantity;
                    } else {
                         // In progress
                         $daysPassed = now()->diffInDays($start) + 1; // +1 to include start day
                         $pQty = $daysPassed * ($item->estimated_daily_qty ?? 0);
                         // Cap at total
                         if ($pQty > $item->total_quantity) $pQty = $item->total_quantity;
                    }
                    $projPlanned += $pQty;
                }
            }
            
            $percentage = $projTotal > 0 ? round(($projCompleted / $projTotal) * 100, 1) : 0;
            $projectNames[] = $project->name;
            $projectProgressValues[] = $percentage;

            $totalQty += $projTotal;
            $totalCompleted += $projCompleted;
            $plannedData += $projPlanned;
            $actualData += $projCompleted;
        }

        $overallCompletion = $totalQty > 0 ? round(($totalCompleted / $totalQty) * 100, 1) : 0;

        // Populate dropdowns
        $employeesList = Employee::select('id', 'name')->get();
        $projectTypesList = ProjectType::all();
        $projectsList = ProjectProgress::select('id', 'name')->get();
        $itemsList = WorkItem::select('id', 'name')->get();

        return view('progress::dashboard', compact(
            'projects',
            'totalEmployees',
            'totalProjects',
            'overallCompletion',
            'plannedData',
            'actualData',
            'projectNames',
            'projectProgressValues',
            'statusCounts',
            'employeesList',
            'projectTypesList',
            'projectsList',
            'itemsList'
        ));
    }
}
