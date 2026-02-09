<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\ProjectProgress as Project;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\DailyProgress;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\Employee;
use Modules\Progress\Models\ProjectType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-dashboard');
    }

    public function index(Request $request)
    {
        try {
            \Log::info('Progress Dashboard accessed', [
                'user' => auth()->id(),
                'request' => $request->all()
            ]);
            
            // قاعدة الاستعلام الأساسية
            $query = Project::query()->where('is_draft', false);

    // فلترة حسب الحالة
    if ($request->filled('status') && $request->status != 'all') {
        $query->where('status', $request->status);
    }

    // فلترة حسب الموظف
    if ($request->filled('employee_id')) {
        $query->whereHas('employees', function($q) use ($request) {
            $q->where('employees.id', $request->employee_id);
        });
    }

    // فلترة حسب نوع المشروع
    if ($request->filled('type_id')) {
        $query->where('project_type_id', $request->type_id);
    }

    // فلترة حسب المشروع المحدد
    if ($request->filled('project_id')) {
        $query->where('id', $request->project_id);
    }

    // فلترة حسب البند
    if ($request->filled('item_id')) {
        $query->whereHas('items', function($q) use ($request) {
            $q->where('work_item_id', $request->item_id);
        });
    }

    // فلترة حسب النطاق الزمني
    if ($request->filled('date_range') && is_numeric($request->date_range)) {
        $query->where('created_at', '>=', now()->subDays((int)$request->date_range));
    }

    // جلب المشاريع مع حساب التقدم
    // استخدام الدالة الموحدة من Project Model
    $projects = $query->with(['items'])->get()->map(function ($project) {
        $project->progress = $project->overall_progress;
        $project->total_quantity = $project->items->sum('total_quantity');
        return $project;
    });

    // الحسابات العامة بناءً على الفلاتر
    $filteredProjects = $projects;
    $progress = $this->calculateFilteredProgress($filteredProjects);
    $itemsCount = WorkItem::count();
    $dailyFormsCount = DailyProgress::distinct('progress_date')->count();
    $projectsCount = (clone $query)->count();
    $teamMembersCount = Employee::count();

    // بيانات المخططات بناءً على الفلاتر
    $projectProgressData = $this->getProjectProgressData(clone $query);
    $projectStatusData = $this->getProjectStatusData(clone $query);
    $plannedVsActualData = $this->getPlannedVsActualData(clone $query);
    // باقي البيانات للفلترة
    $teamMembers = Employee::all();
    $projectTypes = ProjectType::all();
    $allProjects = Project::all();
    $allItems = WorkItem::all();

    // إذا كان الطلب AJAX، إرجاع بيانات JSON
    if ($request->ajax()) {
        return response()->json([
            'itemsCount' => $itemsCount,
            'dailyFormsCount' => $dailyFormsCount,
            'projectsCount' => $projectsCount,
            'progress' => $progress,
            'projectProgressData' => $projectProgressData,
            'plannedVsActualData' => $plannedVsActualData,
            'projectStatusData' => $projectStatusData,
            'projects' => $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'progress' => $project->progress,
                    'status' => $project->status
                ];
            })
        ]);
    }

    return view('progress::dashboard', compact(
        'progress',
        'itemsCount',
        'dailyFormsCount',
        'projectsCount',
        'projectProgressData',
        'projectStatusData',
        'projects',
        'teamMembers',
        'projectTypes',
        'allProjects',
        'allItems',
        'request',
        'plannedVsActualData', // Add this line
        'teamMembersCount'
    ));
    
    } catch (\Exception $e) {
        if ($request->ajax()) {
            return response()->json([
                'error' => true,
                'message' => 'Error occurred while filtering: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->with('error', 'Error occurred: ' . $e->getMessage());
    }
}

    private function getPlannedVsActualData($query = null)
{
    $projects = $query ? $query->with(['items', 'dailyProgress'])->get() : Project::with(['items', 'dailyProgress'])->get();

    $labels = [];
    $planned = [];
    $actual = [];

    foreach ($projects as $project) {
        // Calculate actual progress
        $totalQuantity = $project->items->sum('total_quantity');
        $completedQuantity = $project->dailyProgress->sum('quantity');
        $actualProgress = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100) : 0;

        // Calculate planned progress based on timeline
        $plannedProgress = 0;
        if ($project->start_date && $project->end_date) {
            $startDate = Carbon::parse($project->start_date);
            $endDate = Carbon::parse($project->end_date);
            $today = Carbon::today();

            if ($today->greaterThanOrEqualTo($endDate)) {
                $plannedProgress = 100;
            } elseif ($today->greaterThanOrEqualTo($startDate)) {
                $totalDays = $startDate->diffInDays($endDate);
                $daysPassed = $startDate->diffInDays($today);
                $plannedProgress = min(100, round(($daysPassed / $totalDays) * 100));
            }
        }

        $labels[] = $project->name;
        $planned[] = $plannedProgress;
        $actual[] = $actualProgress;
    }

    return [
        'labels' => $labels,
        'planned' => $planned,
        'actual' => $actual
    ];
}
private function getProjectProgressData($query = null)
{
    $projects = $query ? $query->with(['items', 'dailyProgress'])->get() : Project::with(['items', 'dailyProgress'])->get();

    $labels = [];
    $data = [];

    foreach ($projects as $project) {
        $totalQuantity = $project->items->sum('total_quantity');
        $completedQuantity = $project->dailyProgress->sum('quantity');

        $labels[] = $project->name;
        $data[] = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100) : 0;
    }

    return [
        'labels' => $labels,
        'data' => $data
    ];
}

private function getProjectStatusData($query = null)
{
    if ($query) {
        $active = (clone $query)->where('status', 'active')->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
    } else {
        $active = Project::where('status', 'active')->count();
        $completed = Project::where('status', 'completed')->count();
        $pending = Project::where('status', 'pending')->count();
    }

    return [$active, $completed, $pending];
}
    private function calculateOverallProgress()
    {
        // ✅ فقط project items (ليس template items)
        $totalQuantity = ProjectItem::whereNotNull('project_id')->sum('total_quantity');
        // استخدام completed_quantity المخزن بدلاً من إعادة الحساب من daily_progress
        $completedQuantity = ProjectItem::whereNotNull('project_id')->sum('completed_quantity');

        return $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100) : 0;
    }

    private function calculateFilteredProgress($projects)
    {
        if ($projects->isEmpty()) {
            return 0;
        }
        
        $totalQuantity = $projects->sum('total_quantity');
        $completedQuantity = 0;
        
        // استخدام completed_quantity المخزن من البنود بدلاً من daily_progress
        foreach ($projects as $project) {
            if ($project->items) {
                $completedQuantity += $project->items->sum('completed_quantity');
            }
        }

        return $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100) : 0;
    }
    // private function getProjectProgressData()
    // {
    //     $projects = Project::with(['items', 'dailyProgress'])->get();

    //     $labels = [];
    //     $data = [];

    //     foreach ($projects as $project) {
    //         $totalQuantity = $project->items->sum('total_quantity');
    //         $completedQuantity = $project->dailyProgress->sum('quantity');

    //         $labels[] = $project->name;
    //         $data[] = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100) : 0;
    //     }

    //     return [
    //         'labels' => $labels,
    //         'data' => $data
    //     ];
    // }

    // private function getProjectStatusData()
    // {
    //     return [
    //         Project::where('status', 'active')->count(),
    //         Project::where('status', 'completed')->count(),
    //         Project::where('status', 'pending')->count()
    //     ];
    // }
}
