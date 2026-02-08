<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\Project;
use Modules\Progress\Models\Client;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\Employee;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\WorkItemCategory;
use Modules\Progress\Models\ItemStatus;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Http\Requests\StoreProjectRequest;
use Modules\Progress\Http\Requests\UpdateProjectRequest;
use Modules\Progress\Http\Requests\ProjectProgressRequest;
use Modules\Progress\Services\ProjectService;
use Modules\Progress\Repositories\ProjectRepository;
use Modules\Progress\Repositories\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;

class ProjectController extends Controller
{
    protected ProjectService $projectService;
    protected ProjectRepository $projectRepository;
    protected EmployeeRepository $employeeRepository;

    public function __construct(
        ProjectService $projectService,
        ProjectRepository $projectRepository,
        EmployeeRepository $employeeRepository
    ) {
        $this->projectService = $projectService;
        $this->projectRepository = $projectRepository;
        $this->employeeRepository = $employeeRepository;
        
        // Permission middleware
        $this->middleware('can:view progress-projects')->only(['index', 'show', 'drafts', 'ganttChart', 'ganttData', 'getItemsData', 'progress', 'dashboard', 'dashboardPrint', 'getSubprojects']);
        $this->middleware('can:create progress-projects')->only(['create', 'store']);
        $this->middleware('can:edit progress-projects')->only(['edit', 'update', 'publish', 'copy', 'saveAsTemplate', 'updateItemStatus', 'updateSubprojectWeight', 'updateAllSubprojectsWeight']);
        $this->middleware('can:delete progress-projects')->only(['destroy']);
        $this->middleware('can:export progress-projects')->only(['export']);
    }

    /**
     * عرض قائمة المشاريع
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            // الأدمن والمدير يشوفوا كل المشاريع النشطة
            $projects = $this->projectRepository->getAllActive();
        } else {
            // باقي المستخدمين يشوفوا المشاريع المرتبطة بيهم (user_id يستخدم كـ employee_id)
            $projects = $this->projectRepository->getByUserId($user->id, false);
        }

        return view('progress::projects.index', compact('projects'));
    }

    /**
     * عرض قائمة المسودات
     */
    public function drafts()
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            // الأدمن والمدير يشوفوا كل المسودات
            $drafts = $this->projectRepository->getAllDrafts();
        } else {
            // باقي المستخدمين يشوفوا المسودات المرتبطة بيهم (user_id يستخدم كـ employee_id)
            $drafts = $this->projectRepository->getByUserId($user->id, true);
        }

        return view('progress::projects.drafts', compact('drafts'));
    }

    /**
     * عرض نموذج إنشاء مشروع جديد
     */
    public function create()
    {
        $clients = Client::orderBy('cname')->get();
        $workItems = WorkItem::with('category')->orderBy('name')->get();
        $employees = $this->employeeRepository->getAll();
        $templates = ProjectTemplate::withCount('items')->orderBy('name')->get();
        $projectTypes = ProjectType::orderBy('name')->get();
        $categories = WorkItemCategory::orderBy('name')->get();
        
        // Initialize empty collections for new project
        $projectItems = collect([]);
        $subprojects = collect([]);

        return view('progress::projects.create', compact(
            'clients',
            'workItems',
            'employees',
            'templates',
            'projectTypes',
            'categories',
            'projectItems',
            'subprojects'
        ));
    }

    /**
     * حفظ مشروع جديد
     */
    public function store(StoreProjectRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // ✅ إضافة save_as_draft إلى البيانات المعتمدة
            $validated['save_as_draft'] = $request->input('save_as_draft');
            
            $project = $this->projectService->createProject($validated);

            $message = !empty($validated['save_as_draft'])
                ? __('general.draft_saved_successfully')
                : __('general.project_created_successfully');

            return redirect()
                ->route('progress.projects.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('general.error_creating_project') . ': ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل المشروع
     */
    public function show(Project $project)
    {
        $project = $this->projectRepository->getProjectWithProgress($project);

        // Load relationships
        $project->load([
            'subprojects',
            'items.workItem.category',
            'items.subproject',
            'client',
            'employees',
            'type'
        ]);

        // Group items by subproject
        $itemsBySubproject = $project->items->groupBy(function ($item) {
            return $item->subproject_name ?? __('general.without_subproject');
        });

        // Calculate overall progress
        // استخدام الدالة الموحدة من Model التي تتعامل بشكل صحيح مع البنود المكررة
        $overallProgress = $project->overall_progress;

        $workItems = WorkItem::with('category')->orderBy('name')->get();
        $employees = $this->employeeRepository->getAll();
        $categories = WorkItemCategory::with('workItems')->orderBy('name')->get();
        
        // Get subprojects with items (includes virtual "بدون فرعي" subproject)
        $subprojectsWithItems = $this->getSubprojectsWithItems($project);
        
        return view('progress::projects.show', compact(
            'project',
            'itemsBySubproject',
            'workItems',
            'employees',
            'categories',
            'overallProgress',
            'subprojectsWithItems'
        ));
    }

    /**
     * عرض لوحة تحكم المشروع
     */
    public function dashboard(Project $project, Request $request)
    {
        $project = $this->projectRepository->getProjectWithProgress($project);
        
        // Load relationships
        $project->load([
            'subprojects',
            'items.workItem.category',
            'items.subproject',
            'client',
            'employees',
            'type'
        ]);

        // Group items by subproject and then by category
        $itemsBySubproject = $project->items->groupBy(function ($item) {
            return $item->subproject_name ?? __('general.without_subproject');
        });

        // Create hierarchical structure: Subprojects → Categories → Items
        $hierarchicalData = [];
        
        // ✅ Get only subprojects that have items
        $subprojects = $this->getSubprojectsWithItems($project);
        
        foreach ($subprojects as $subproject) {
            // Check if this is the virtual "بدون فرعي" subproject
            $withoutSubprojectName = __('general.without_subproject');
            $isWithoutSubproject = ($subproject->name === $withoutSubprojectName);
            
            if ($isWithoutSubproject) {
                // Get items without subproject (where subproject_name is null or empty)
                $subprojectItems = $project->items->filter(function ($item) {
                    return empty($item->subproject_name);
                })->values();
            } else {
                // Get items for regular subprojects
                $subprojectItems = $project->items->where('subproject_name', $subproject->name)->values();
            }
            
            // Skip if no items found
            if ($subprojectItems->isEmpty()) {
                continue;
            }
            
            // Calculate subproject progress
            $totalQuantity = $subprojectItems->sum('total_quantity');
            $completedQuantity = $subprojectItems->sum('completed_quantity');
            $progress = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100, 2) : 0;
            
            // Group items by category
            $itemsByCategory = $subprojectItems->groupBy(function ($item) {
                return $item->workItem->category->name ?? __('general.uncategorized');
            });
            
            // Build categories array keyed by category name
            $categoriesData = [];
            foreach ($itemsByCategory as $categoryName => $categoryItems) {
                $catTotal = $categoryItems->sum('total_quantity');
                $catCompleted = $categoryItems->sum('completed_quantity');
                $catProgress = $catTotal > 0 ? round(($catCompleted / $catTotal) * 100, 2) : 0;
                
                $categoriesData[$categoryName] = [
                    'name' => $categoryName,
                    'items' => $categoryItems->values(), // Ensure items is a collection with numeric keys
                    'progress' => $catProgress,
                    'total_quantity' => $catTotal,
                    'completed_quantity' => $catCompleted,
                    'count' => $categoryItems->count()
                ];
            }
            
            $hierarchicalData[$subproject->name] = [
                'subproject' => $subproject,
                'progress' => $progress,
                'total_quantity' => $totalQuantity,
                'completed_quantity' => $completedQuantity,
                'weight' => $subproject->weight ?? 0,
                'categories' => $categoriesData
            ];
        }
        

        // Calculate overall progress
        $overallProgress = $project->overall_progress;

        // Calculate additional statistics
        $totalItems = $project->items->count();
        $totalEmployees = $project->employees->count();
        
        // Calculate days passed and remaining
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $today = Carbon::today();
        
        $daysPassed = $today->gte($projectStartDate) ? $projectStartDate->diffInDays($today) : 0;
        $daysRemaining = $today->lte($projectEndDate) ? $today->diffInDays($projectEndDate) : 0;
        
        // Calculate planned progress until today
        // Calculate planned progress until today (Weighted Quantity-based)
        $plannedProgressUntilToday = 0;
        $weeklyHolidays = $project->weekly_holidays ? explode(',', $project->weekly_holidays) : [];

        if ($project->subprojects->count() > 0 && $project->subprojects->where('weight', '>', 0)->count() > 0) {
            // Weighted Logic
            $subprojects = $this->getSubprojectsWithItems($project);
            foreach ($subprojects as $subproject) {
                if ($subproject->weight <= 0) continue;

                $subprojectItems = $project->items->where('subproject_name', $subproject->name);
                $measurableItems = $subprojectItems->filter(function ($item) {
                    return $item->is_measurable ?? false;
                });
                
                if ($measurableItems->isEmpty()) continue;

                $subTotalQuantity = $measurableItems->sum('total_quantity');
                $subPlannedQuantity = 0;
                
                foreach ($measurableItems as $item) {
                    $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                    $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                    
                    if ($today->gte($itemStartDate)) {
                        $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $today, $weeklyHolidays);
                        $itemPlannedTotal = $dailyPlannedQuantity > 0 
                            ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                            : 0;
                        $subPlannedQuantity += $itemPlannedTotal;
                    }
                }
                
                $subPlannedProgress = $subTotalQuantity > 0 
                    ? ($subPlannedQuantity / $subTotalQuantity) * 100 
                    : 0;
                    
                $plannedProgressUntilToday += $subPlannedProgress * ($subproject->weight / 100);
            }
            $plannedProgressUntilToday = round($plannedProgressUntilToday, 2);
        } else {
            // Unweighted Logic (Total Planned / Total)
            $measurableItems = $project->items->filter(function ($item) {
                return $item->is_measurable ?? false;
            });
            
            $totalQuantity = $measurableItems->sum('total_quantity');
            $totalPlannedQuantity = 0;
            
            foreach ($measurableItems as $item) {
                $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                
                if ($today->gte($itemStartDate)) {
                    $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $today, $weeklyHolidays);
                    $itemPlannedTotal = $dailyPlannedQuantity > 0 
                        ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                        : 0;
                    $totalPlannedQuantity += $itemPlannedTotal;
                }
            }
            
            $plannedProgressUntilToday = $totalQuantity > 0 
                ? min(100, round(($totalPlannedQuantity / $totalQuantity) * 100, 2)) 
                : 0;
        }
        
        // Actual progress is the overall progress
        $actualProgressUntilToday = $overallProgress;
        
        // Calculate difference
        $progressDifference = $actualProgressUntilToday - $plannedProgressUntilToday;

        // Get recent progress grouped by date
        $recentProgress = \Modules\Progress\Models\DailyProgress::whereHas('projectItem', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->with(['projectItem.workItem.category', 'employee'])
            ->orderBy('progress_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($progress) {
                $date = $progress->progress_date;
                return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
            });

        // Get chart data
        $chartData = $this->getProjectChartData($project);
        
        // Get additional chart data for different views
        $subprojectsChartData = $this->getSubprojectsChartData($project);
        $categoriesChartData = $this->getCategoriesChartData($project);
        $subprojectItemsChartData = $this->getSubprojectItemsChartData($project);
        $categoryItemsChartData = $this->getCategoryItemsChartData($project);

        // Project status with color, icon, and message
        $projectStatus = $this->getProjectStatusInfo($project->status);
        
        // Get subprojects with items (includes virtual "بدون فرعي" subproject)
        $subprojectsWithItems = $this->getSubprojectsWithItems($project);

        // Get all active item statuses for the status dropdown and filter
        $itemStatuses = ItemStatus::active()->ordered()->get();

        return view('progress::projects.dashboard', compact(
            'project',
            'itemsBySubproject',
            'hierarchicalData',
            'overallProgress',
            'totalItems',
            'totalEmployees',
            'daysPassed',
            'daysRemaining',
            'recentProgress',
            'chartData',
            'subprojectsChartData',
            'categoriesChartData',
            'subprojectItemsChartData',
            'categoryItemsChartData',
            'projectStatus',
            'plannedProgressUntilToday',
            'actualProgressUntilToday',
            'progressDifference',
            'today',
            'subprojectsWithItems',
            'itemStatuses'
        ));
    }

    /**
     * Print dashboard view (raw page without sidebar/topbar)
     */
    public function dashboardPrint(Project $project, Request $request)
    {
        $project = $this->projectRepository->getProjectWithProgress($project);

        // Load relationships
        $project->load([
            'subprojects',
            'items.workItem.category',
            'items.subproject',
            'client',
            'employees',
            'type'
        ]);

        // Get visibility settings from query parameters
        $visibleComponents = $request->get('components', []);
        if (is_string($visibleComponents)) {
            $visibleComponents = json_decode($visibleComponents, true) ?? [];
        }

        // Group items by subproject and then by category
        $itemsBySubproject = $project->items->groupBy(function ($item) {
            return $item->subproject_name ?? __('general.without_subproject');
        });

        // Create hierarchical structure: Subprojects → Categories → Items
        $hierarchicalData = [];
        
        // ✅ Get only subprojects that have items
        $subprojects = $this->getSubprojectsWithItems($project);
        
        foreach ($subprojects as $subproject) {
            // Check if this is the virtual "بدون فرعي" subproject
            $withoutSubprojectName = __('general.without_subproject');
            $isWithoutSubproject = ($subproject->name === $withoutSubprojectName);
            
            if ($isWithoutSubproject) {
                // Get items without subproject (where subproject_name is null or empty)
                $subprojectItems = $project->items->filter(function ($item) {
                    return empty($item->subproject_name);
                })->values();
            } else {
                // Get items for regular subprojects
                $subprojectItems = $project->items->where('subproject_name', $subproject->name)->values();
            }
            
            // Skip if no items found
            if ($subprojectItems->isEmpty()) {
                continue;
            }
            
            // Calculate subproject progress
            $totalQuantity = $subprojectItems->sum('total_quantity');
            $completedQuantity = $subprojectItems->sum('completed_quantity');
            $progress = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100, 2) : 0;
            
            // Group items by category
            $itemsByCategory = $subprojectItems->groupBy(function ($item) {
                return $item->workItem->category->name ?? __('general.uncategorized');
            });
            
            // Build categories array keyed by category name
            $categoriesData = [];
            foreach ($itemsByCategory as $categoryName => $categoryItems) {
                $catTotal = $categoryItems->sum('total_quantity');
                $catCompleted = $categoryItems->sum('completed_quantity');
                $catProgress = $catTotal > 0 ? round(($catCompleted / $catTotal) * 100, 2) : 0;
                
                $categoriesData[$categoryName] = [
                    'name' => $categoryName,
                    'items' => $categoryItems->values(),
                    'progress' => $catProgress,
                    'total_quantity' => $catTotal,
                    'completed_quantity' => $catCompleted,
                    'count' => $categoryItems->count()
                ];
            }
            
            $hierarchicalData[$subproject->name] = [
                'subproject' => $subproject,
                'progress' => $progress,
                'total_quantity' => $totalQuantity,
                'completed_quantity' => $completedQuantity,
                'weight' => $subproject->weight ?? 0,
                'categories' => $categoriesData
            ];
        }
        

        // Calculate overall progress
        $overallProgress = $project->overall_progress;

        // Calculate additional statistics
        $totalItems = $project->items->count();
        $totalEmployees = $project->employees->count();
        
        // Calculate days passed and remaining
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $today = Carbon::today();
        
        $daysPassed = $today->gte($projectStartDate) ? $projectStartDate->diffInDays($today) : 0;
        $daysRemaining = $today->lte($projectEndDate) ? $today->diffInDays($projectEndDate) : 0;
        
        // Calculate planned progress until today
        $plannedProgressUntilToday = 0;
        if ($project->start_date && $project->end_date) {
            $totalDays = $projectStartDate->diffInDays($projectEndDate);
            if ($totalDays > 0) {
                if ($today->greaterThanOrEqualTo($projectEndDate)) {
                    $plannedProgressUntilToday = 100;
                } elseif ($today->greaterThanOrEqualTo($projectStartDate)) {
                    $plannedProgressUntilToday = min(100, round(($daysPassed / $totalDays) * 100, 2));
                }
            }
        }
        
        // Actual progress is the overall progress
        $actualProgressUntilToday = $overallProgress;
        
        // Calculate difference
        $progressDifference = $actualProgressUntilToday - $plannedProgressUntilToday;

        // Get recent progress grouped by date
        $recentProgress = \Modules\Progress\Models\DailyProgress::whereHas('projectItem', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->with(['projectItem.workItem.category', 'employee'])
            ->orderBy('progress_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($progress) {
                $date = $progress->progress_date;
                return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
            });

        // Get chart data
        $chartData = $this->getProjectChartData($project);
        
        // Get additional chart data for different views
        $subprojectsChartData = $this->getSubprojectsChartData($project);
        $categoriesChartData = $this->getCategoriesChartData($project);
        $subprojectItemsChartData = $this->getSubprojectItemsChartData($project);
        $categoryItemsChartData = $this->getCategoryItemsChartData($project);

        // Project status with color, icon, and message
        $projectStatus = $this->getProjectStatusInfo($project->status);
        
        // Get subprojects with items (includes virtual "بدون فرعي" subproject)
        $subprojectsWithItems = $this->getSubprojectsWithItems($project);

        // Get all active item statuses for the status dropdown and filter
        $itemStatuses = ItemStatus::active()->ordered()->get();

        // Calculate total weighted progress for print view
        $totalWeightedProgress = null;
        $totalPlannedWeightedProgress = null;
        if ($project->subprojects->count() > 0 && $project->subprojects->where('weight', '>', 0)->count() > 0) {
            // Actual Weighted Progress (from Model)
            $totalWeightedProgress = $project->overall_progress;

            // Planned Weighted Progress
            $totalPlannedWeightedProgress = 0;
            $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
            $todayObj = Carbon::today();
            $weeklyHolidays = $project->weekly_holidays 
                ? explode(',', $project->weekly_holidays) 
                : [];
            
            // Get subprojects with weights
            $subprojects = $this->getSubprojectsWithItems($project);

            foreach ($subprojects as $subproject) {
                if ($subproject->weight <= 0) continue;

                $subprojectItems = $project->items->where('subproject_name', $subproject->name);
                $measurableItems = $subprojectItems->filter(function ($item) {
                    return $item->is_measurable ?? false;
                });
                
                if ($measurableItems->isEmpty()) continue;

                $subTotalQuantity = $measurableItems->sum('total_quantity');
                $subPlannedQuantity = 0;
                
                foreach ($measurableItems as $item) {
                    $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                    $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                    
                    if ($todayObj->gte($itemStartDate)) {
                        $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                        $itemPlannedTotal = $dailyPlannedQuantity > 0 
                            ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                            : 0;
                        $subPlannedQuantity += $itemPlannedTotal;
                    }
                }
                
                $subPlannedProgress = $subTotalQuantity > 0 
                    ? ($subPlannedQuantity / $subTotalQuantity) * 100 
                    : 0;
                    
                $totalPlannedWeightedProgress += $subPlannedProgress * ($subproject->weight / 100);
            }
            
            $totalPlannedWeightedProgress = round($totalPlannedWeightedProgress, 2);
        } else {
             // Fallback to simple calculation (Classic Mode)
            $subprojects = $this->getSubprojectsWithItems($project);
            $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
            $todayObj = Carbon::today();
            $weeklyHolidays = $project->weekly_holidays 
                ? explode(',', $project->weekly_holidays) 
                : [];
            
            $totalQuantity = 0;
            $totalCompletedQuantity = 0;
            $totalPlannedQuantity = 0;
            
            foreach ($subprojects as $subproject) {
                $subprojectItems = $project->items->where('subproject_name', $subproject->name);
                $measurableItems = $subprojectItems->filter(function ($item) {
                    return $item->is_measurable ?? false;
                });
                
                $subTotalQuantity = $measurableItems->sum('total_quantity');
                $subCompletedQuantity = $measurableItems->sum('completed_quantity');
                
                $subPlannedQuantity = 0;
                foreach ($measurableItems as $item) {
                    $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                    $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                    
                    if ($todayObj->gte($itemStartDate)) {
                        $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                        $itemPlannedTotal = $dailyPlannedQuantity > 0 
                            ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                            : 0;
                        $subPlannedQuantity += $itemPlannedTotal;
                    }
                }
                
                $totalQuantity += $subTotalQuantity;
                $totalCompletedQuantity += $subCompletedQuantity;
                $totalPlannedQuantity += $subPlannedQuantity;
            }
            
            $totalWeightedProgress = $totalQuantity > 0 
                ? min(100, round(($totalCompletedQuantity / $totalQuantity) * 100, 2)) 
                : 0;
            $totalPlannedWeightedProgress = $totalQuantity > 0 
                ? min(100, round(($totalPlannedQuantity / $totalQuantity) * 100, 2)) 
                : 0;
        }

        return view('progress::projects.dashboard-print', compact(
            'project',
            'itemsBySubproject',
            'hierarchicalData',
            'overallProgress',
            'totalItems',
            'totalEmployees',
            'daysPassed',
            'daysRemaining',
            'recentProgress',
            'chartData',
            'subprojectsChartData',
            'categoriesChartData',
            'subprojectItemsChartData',
            'categoryItemsChartData',
            'projectStatus',
            'plannedProgressUntilToday',
            'actualProgressUntilToday',
            'progressDifference',
            'today',
            'subprojectsWithItems',
            'itemStatuses',
            'visibleComponents',
            'totalWeightedProgress',
            'totalPlannedWeightedProgress'
        ));
    }

    /**
     * الحصول على معلومات حالة المشروع
     */
    private function getProjectStatusInfo($status)
    {
        $statuses = [
            'active' => [
                'color' => 'success',
                'icon' => 'play-circle',
                'message' => __('general.active')
            ],
            'completed' => [
                'color' => 'primary',
                'icon' => 'check-circle',
                'message' => __('general.completed')
            ],
            'pending' => [
                'color' => 'warning',
                'icon' => 'clock',
                'message' => __('general.pending')
            ],
            'suspended' => [
                'color' => 'danger',
                'icon' => 'pause-circle',
                'message' => __('general.suspended')
            ],
        ];

        return $statuses[$status] ?? [
            'color' => 'secondary',
            'icon' => 'question-circle',
            'message' => __('general.unknown')
        ];
    }

    /**
     * الحصول على بيانات المخططات للمشروع
     */
    private function getProjectChartData(Project $project)
    {
        // Get daily progress data for the last 30 days
        $startDate = Carbon::today()->subDays(30)->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        $dailyProgress = DB::table('daily_progress')
            ->join('project_items', 'daily_progress.project_item_id', '=', 'project_items.id')
            ->where('project_items.project_id', $project->id)
            ->whereBetween('daily_progress.progress_date', [$startDate, $endDate])
            ->whereNull('daily_progress.deleted_at')
            ->select(
                DB::raw('DATE(daily_progress.progress_date) as date'),
                DB::raw('SUM(daily_progress.quantity) as total_quantity')
            )
            ->groupBy(DB::raw('DATE(daily_progress.progress_date)'))
            ->orderBy('date')
            ->get();

        // Get work items progress data with weighted ratio
        $project->load(['items.workItem', 'subprojects']);
        $workItems = [];
        $workItemsFlat = [];
        $completionPercentages = [];
        $weightedRatios = [];
        $plannedWeightedRatios = [];
        $groupedData = [];
        
        // Get project dates and holidays for planned calculation
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $todayObj = Carbon::today();
        $weeklyHolidays = $project->weekly_holidays 
            ? explode(',', $project->weekly_holidays) 
            : [];
        
        // Create subproject weight map
        $subprojectWeights = [];
        foreach ($project->subprojects as $subproject) {
            $subprojectWeights[$subproject->name] = $subproject->weight ?? 0;
        }
        
        // Get all items (not filtered by measurable)
        $allItems = $project->items->filter(function ($item) {
            return $item->workItem;
        });
        
        // Prepare grouped data by subproject
        $itemsBySubproject = $allItems->groupBy(function ($item) {
            return $item->subproject_name ?? __('general.without_subproject');
        });
        
        foreach ($itemsBySubproject as $subprojectName => $subprojectItems) {
            $groupedData[$subprojectName] = [
                'items' => [],
                'labels' => []
            ];
            
            foreach ($subprojectItems as $item) {
                // Build label with subproject name and notes
                $label = $item->workItem->name;
                if (!empty($item->notes)) {
                    $label .= ' (' . $item->notes . ')';
                }
                
                $workItemsFlat[] = $label;
                
                // Calculate completion percentage
                $percentage = $item->total_quantity > 0 
                    ? round(($item->completed_quantity / $item->total_quantity) * 100, 2)
                    : 0;
                $completionPercentages[] = $percentage;
                
                // No weight calculation - use progress directly
                $weightedRatios[] = round($percentage, 2);
                
                // Calculate planned progress for this item
                $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                
                $plannedTotalQuantity = 0;
                if ($todayObj->gte($itemStartDate)) {
                    $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                    $plannedTotalQuantity = $dailyPlannedQuantity > 0 
                        ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                        : 0;
                }
                
                $plannedProgress = $item->total_quantity > 0 
                    ? min(100, round(($plannedTotalQuantity / $item->total_quantity) * 100, 2)) 
                    : 0;
                
                // No weight calculation - use planned progress directly
                $plannedWeightedRatios[] = round($plannedProgress, 2);
                
                // Store in grouped data with subproject name prefix
                $groupedLabel = $subprojectName . ' - ' . $label;
                $groupedData[$subprojectName]['items'][] = [
                    'label' => $groupedLabel,
                    'percentage' => $percentage,
                    'planned_progress' => $plannedProgress
                ];
                $groupedData[$subprojectName]['labels'][] = $groupedLabel;
            }
        }
        
        // Set workItems to flat list by default
        $workItems = $workItemsFlat;

        // Get weekly progress (last 7 days grouped by week day)
        $weeklyProgress = DB::table('daily_progress')
            ->join('project_items', 'daily_progress.project_item_id', '=', 'project_items.id')
            ->where('project_items.project_id', $project->id)
            ->whereBetween('daily_progress.progress_date', [$startDate, $endDate])
            ->whereNull('daily_progress.deleted_at')
            ->select(
                DB::raw('DAYOFWEEK(daily_progress.progress_date) as day_of_week'),
                DB::raw('SUM(daily_progress.quantity) as total_quantity')
            )
            ->groupBy(DB::raw('DAYOFWEEK(daily_progress.progress_date)'))
            ->orderBy('day_of_week')
            ->get()
            ->pluck('total_quantity', 'day_of_week')
            ->toArray();

        // Fill missing days with 0
        $weeklyProgressArray = [];
        $dayNames = ['', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        for ($i = 1; $i <= 7; $i++) {
            $weeklyProgressArray[] = (float) ($weeklyProgress[$i] ?? 0);
        }

        return [
            'daily' => $dailyProgress->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('Y-m-d'),
                    'quantity' => (float) $item->total_quantity
                ];
            })->toArray(),
            'work_items' => $workItems,
            'completion_percentages' => $completionPercentages,
            'weighted_ratios' => $weightedRatios,
            'planned_weighted_ratios' => $plannedWeightedRatios,
            'weekly_progress' => $weeklyProgressArray,
            'day_names' => array_slice($dayNames, 1) // Remove empty first element
        ];
    }

    /**
     * الحصول على بيانات المخططات لجميع المشاريع الفرعية
     */
    private function getSubprojectsChartData(Project $project)
    {
        // ✅ Get only subprojects that have items
        $subprojects = $this->getSubprojectsWithItems($project);
        $labels = [];
        $completionPercentages = [];
        $weightedRatios = [];
        $plannedWeightedRatios = [];
        
        // Get project dates and holidays for planned calculation
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $todayObj = Carbon::today();
        $weeklyHolidays = $project->weekly_holidays 
            ? explode(',', $project->weekly_holidays) 
            : [];
        
        $withoutSubprojectName = __('general.without_subproject');
        
        foreach ($subprojects as $subproject) {
            // Check if this is the virtual "بدون فرعي" subproject
            $isWithoutSubproject = ($subproject->id === null && $subproject->name === $withoutSubprojectName);
            
            // Get items for this subproject
            if ($isWithoutSubproject) {
                // Get items without subproject (where subproject_name is null or empty)
                $subprojectItems = $project->items->filter(function ($item) {
                    return empty($item->subproject_name);
                });
            } else {
                // Get items for regular subprojects
                $subprojectItems = $project->items->where('subproject_name', $subproject->name);
            }
            
            // Filter only measurable items for this subproject
            $measurableItems = $subprojectItems->filter(function ($item) {
                return $item->is_measurable ?? false;
            });
            
            // Skip if no measurable items
            if ($measurableItems->isEmpty()) {
                continue;
            }
            
            $totalQuantity = $measurableItems->sum('total_quantity');
            $completedQuantity = $measurableItems->sum('completed_quantity');
            $progress = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100, 2) : 0;
            
            // Calculate planned progress (only for measurable items)
            $plannedTotalQuantity = 0;
            foreach ($measurableItems as $item) {
                $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                
                if ($todayObj->gte($itemStartDate)) {
                    $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                    $itemPlannedTotal = $dailyPlannedQuantity > 0 
                        ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                        : 0;
                    $plannedTotalQuantity += $itemPlannedTotal;
                }
            }
            
            $plannedProgress = $totalQuantity > 0 
                ? min(100, round(($plannedTotalQuantity / $totalQuantity) * 100, 2)) 
                : 0;
            
            // No weight calculation - use progress directly
            $labels[] = $subproject->name;
            $completionPercentages[] = $progress;
            $weightedRatios[] = round($progress, 2);
            $plannedWeightedRatios[] = round($plannedProgress, 2);
        }
        
        return [
            'labels' => $labels,
            'completion_percentages' => $completionPercentages,
            'weighted_ratios' => $weightedRatios,
            'planned_weighted_ratios' => $plannedWeightedRatios
        ];
    }

    /**
     * الحصول على بيانات المخططات لجميع الفئات
     */
    private function getCategoriesChartData(Project $project)
    {
        $project->load(['items.workItem.category', 'subprojects']);
        $categoriesData = [];
        
        // Use all items (not filtered by measurable) for Categories Progress
        foreach ($project->items as $item) {
            if ($item->workItem && $item->workItem->category) {
                $categoryName = $item->workItem->category->name;
                if (!isset($categoriesData[$categoryName])) {
                    $categoriesData[$categoryName] = [
                        'total_quantity' => 0,
                        'completed_quantity' => 0,
                        'items' => []
                    ];
                }
                $categoriesData[$categoryName]['total_quantity'] += $item->total_quantity;
                $categoriesData[$categoryName]['completed_quantity'] += $item->completed_quantity;
                $categoriesData[$categoryName]['items'][] = $item;
            }
        }
        
        // Handle uncategorized items (all items)
        $uncategorizedItems = $project->items->filter(function($item) {
            return !$item->workItem || !$item->workItem->category;
        });
        
        if ($uncategorizedItems->isNotEmpty()) {
            $categoriesData[__('general.uncategorized')] = [
                'total_quantity' => $uncategorizedItems->sum('total_quantity'),
                'completed_quantity' => $uncategorizedItems->sum('completed_quantity'),
                'items' => $uncategorizedItems->values()->all()
            ];
        }
        
        // Get project dates and holidays for planned calculation
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $todayObj = Carbon::today();
        $weeklyHolidays = $project->weekly_holidays 
            ? explode(',', $project->weekly_holidays) 
            : [];
        
        // Create subproject weight map
        $subprojectWeights = [];
        foreach ($project->subprojects as $subproject) {
            $subprojectWeights[$subproject->name] = $subproject->weight ?? 0;
        }
        $withoutSubprojectName = __('general.without_subproject');
        foreach ($project->subprojects as $subproject) {
            if ($subproject->name === $withoutSubprojectName) {
                $subprojectWeights[$withoutSubprojectName] = $subproject->weight ?? 0;
                break;
            }
        }
        
        $labels = [];
        $completionPercentages = [];
        $weightedRatios = [];
        $plannedWeightedRatios = [];
        
        foreach ($categoriesData as $categoryName => $data) {
            $labels[] = $categoryName;
            $progress = $data['total_quantity'] > 0 
                ? round(($data['completed_quantity'] / $data['total_quantity']) * 100, 2) 
                : 0;
            $completionPercentages[] = $progress;
            
            // Calculate planned progress for category (no weight)
            $categoryPlannedTotalQuantity = 0;
            $categoryTotalQuantity = $data['total_quantity'];
            
            foreach ($data['items'] as $item) {
                // Item planned progress
                $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                
                $itemPlannedTotalQuantity = 0;
                if ($todayObj->gte($itemStartDate)) {
                    $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                    $itemPlannedTotalQuantity = $dailyPlannedQuantity > 0 
                        ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                        : 0;
                }
                
                $categoryPlannedTotalQuantity += $itemPlannedTotalQuantity;
            }
            
            $categoryPlannedProgress = $categoryTotalQuantity > 0 
                ? min(100, round(($categoryPlannedTotalQuantity / $categoryTotalQuantity) * 100, 2)) 
                : 0;
            
            // No weight calculation - use progress directly
            $weightedRatios[] = round($progress, 2);
            $plannedWeightedRatios[] = round($categoryPlannedProgress, 2);
        }
        
        return [
            'labels' => $labels,
            'completion_percentages' => $completionPercentages,
            'weighted_ratios' => $weightedRatios,
            'planned_weighted_ratios' => $plannedWeightedRatios
        ];
    }

    /**
     * الحصول على بيانات المخططات لبنود مشروع فرعي محدد
     */
    private function getSubprojectItemsChartData(Project $project)
    {
        // ✅ Get only subprojects that have items
        $subprojects = $this->getSubprojectsWithItems($project);
        $dataBySubproject = [];
        
        // Get project dates and holidays for planned calculation
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $todayObj = Carbon::today();
        $weeklyHolidays = $project->weekly_holidays 
            ? explode(',', $project->weekly_holidays) 
            : [];
        
        foreach ($subprojects as $subproject) {
            $subprojectItems = $project->items->where('subproject_name', $subproject->name)->filter(function ($item) {
                return $item->workItem;
            });
            
            $labels = [];
            $completionPercentages = [];
            $weightedRatios = [];
            $plannedWeightedRatios = [];
            $isMeasurable = [];
            
            // Include all items (not just measurable)
            foreach ($subprojectItems as $item) {
                $label = $item->workItem->name;
                if (!empty($item->notes)) {
                    $label .= ' (' . $item->notes . ')';
                }
                $labels[] = $label;
                
                $isMeasurable[] = $item->is_measurable ?? false;
                
                $percentage = $item->total_quantity > 0 
                    ? round(($item->completed_quantity / $item->total_quantity) * 100, 2)
                    : 0;
                $completionPercentages[] = $percentage;
                
                // No weight - use percentage directly
                $weightedRatios[] = round($percentage, 2);
                
                // Calculate planned progress
                $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                
                $plannedTotalQuantity = 0;
                if ($todayObj->gte($itemStartDate)) {
                    $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                    $plannedTotalQuantity = $dailyPlannedQuantity > 0 
                        ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                        : 0;
                }
                
                $plannedProgress = $item->total_quantity > 0 
                    ? min(100, round(($plannedTotalQuantity / $item->total_quantity) * 100, 2)) 
                    : 0;
                
                // No weight - use planned progress directly
                $plannedWeightedRatios[] = round($plannedProgress, 2);
            }
            
            $dataBySubproject[$subproject->name] = [
                'labels' => $labels,
                'completion_percentages' => $completionPercentages,
                'weighted_ratios' => $weightedRatios,
                'planned_weighted_ratios' => $plannedWeightedRatios,
                'is_measurable' => $isMeasurable
            ];
        }
        
        // Handle items without subproject (all items)
        $itemsWithoutSubproject = $project->items->whereNull('subproject_name')->filter(function ($item) {
            return $item->workItem;
        });
        
        if ($itemsWithoutSubproject->isNotEmpty()) {
            $labels = [];
            $completionPercentages = [];
            $weightedRatios = [];
            $plannedWeightedRatios = [];
            $isMeasurable = [];
            
            // No weight calculation - use progress directly
            $withoutSubprojectName = __('general.without_subproject');
            
            foreach ($itemsWithoutSubproject as $item) {
                $label = $item->workItem->name;
                if (!empty($item->notes)) {
                    $label .= ' (' . $item->notes . ')';
                }
                $labels[] = $label;
                
                $isMeasurable[] = $item->is_measurable ?? false;
                
                $percentage = $item->total_quantity > 0 
                    ? round(($item->completed_quantity / $item->total_quantity) * 100, 2)
                    : 0;
                $completionPercentages[] = $percentage;
                
                // No weight - use percentage directly
                $weightedRatios[] = round($percentage, 2);
                
                // Calculate planned progress
                $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                
                $plannedTotalQuantity = 0;
                if ($todayObj->gte($itemStartDate)) {
                    $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                    $plannedTotalQuantity = $dailyPlannedQuantity > 0 
                        ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                        : 0;
                }
                
                $plannedProgress = $item->total_quantity > 0 
                    ? min(100, round(($plannedTotalQuantity / $item->total_quantity) * 100, 2)) 
                    : 0;
                
                // No weight - use planned progress directly
                $plannedWeightedRatios[] = round($plannedProgress, 2);
            }
            
            $dataBySubproject[$withoutSubprojectName] = [
                'labels' => $labels,
                'completion_percentages' => $completionPercentages,
                'weighted_ratios' => $weightedRatios,
                'planned_weighted_ratios' => $plannedWeightedRatios,
                'is_measurable' => $isMeasurable
            ];
        }
        
        return $dataBySubproject;
    }

    /**
     * الحصول على بيانات المخططات لبنود فئة محددة
     */
    private function getCategoryItemsChartData(Project $project)
    {
        $project->load(['items.workItem.category', 'subprojects']);
        $dataByCategory = [];
        
        // Get project dates and holidays for planned calculation
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        $todayObj = Carbon::today();
        $weeklyHolidays = $project->weekly_holidays 
            ? explode(',', $project->weekly_holidays) 
            : [];
        
        // Create subproject weight map
        $subprojectWeights = [];
        foreach ($project->subprojects as $subproject) {
            $subprojectWeights[$subproject->name] = $subproject->weight ?? 0;
        }
        $withoutSubprojectName = __('general.without_subproject');
        foreach ($project->subprojects as $subproject) {
            if ($subproject->name === $withoutSubprojectName) {
                $subprojectWeights[$withoutSubprojectName] = $subproject->weight ?? 0;
                break;
            }
        }
        
        // Group items by category (all items - not filtered by measurable)
        $itemsByCategory = $project->items->groupBy(function($item) {
            return $item->workItem && $item->workItem->category 
                ? $item->workItem->category->name 
                : __('general.uncategorized');
        });
        
        foreach ($itemsByCategory as $categoryName => $items) {
            $labels = [];
            $completionPercentages = [];
            $weightedRatios = [];
            $plannedWeightedRatios = [];
            
            foreach ($items as $item) {
                if ($item->workItem) {
                    $label = $item->workItem->name;
                    if (!empty($item->subproject_name)) {
                        $label .= ' [' . $item->subproject_name . ']';
                    }
                    if (!empty($item->notes)) {
                        $label .= ' (' . $item->notes . ')';
                    }
                    $labels[] = $label;
                    
                    $percentage = $item->total_quantity > 0 
                        ? round(($item->completed_quantity / $item->total_quantity) * 100, 2)
                        : 0;
                    $completionPercentages[] = $percentage;
                    
                    // No weight calculation - use progress directly
                    $weightedRatios[] = round($percentage, 2);
                    
                    // Calculate planned progress
                    $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                    $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                    $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                    
                    $plannedTotalQuantity = 0;
                    if ($todayObj->gte($itemStartDate)) {
                        $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                        $plannedTotalQuantity = $dailyPlannedQuantity > 0 
                            ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                            : 0;
                    }
                    
                    $plannedProgress = $item->total_quantity > 0 
                        ? min(100, round(($plannedTotalQuantity / $item->total_quantity) * 100, 2)) 
                        : 0;
                    
                    // No weight calculation - use planned progress directly
                    $plannedWeightedRatios[] = round($plannedProgress, 2);
                }
            }
            
            $dataByCategory[$categoryName] = [
                'labels' => $labels,
                'completion_percentages' => $completionPercentages,
                'weighted_ratios' => $weightedRatios,
                'planned_weighted_ratios' => $plannedWeightedRatios
            ];
        }
        
        return $dataByCategory;
    }

    /**
     * عرض صفحة التقدم اليومي للمشروع
     */
    public function progress(Project $project, ProjectProgressRequest $request)
    {
        try {
            // Get validated dates
            $fromDate = $request->getFromDate()->format('Y-m-d');
            $toDate = $request->getToDate()->format('Y-m-d');
            $asOfDate = $request->getAsOfDate()->format('Y-m-d');

            // Cache key based on project and dates
            $cacheKey = "project_progress_{$project->id}_{$fromDate}_{$toDate}_{$asOfDate}";

            // Store cache key for later clearing
            $cacheKeys = Cache::get('project_progress_cache_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('project_progress_cache_keys', $cacheKeys, now()->addDays(7));
            }

            // Try to get from cache, otherwise calculate
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($project, $fromDate, $toDate, $asOfDate) {
                return $this->calculateProjectProgress($project, $fromDate, $toDate, $asOfDate);
            });

            // Apply filters (after cache)
            // Note: total_completed is set on items in calculateProjectProgress()
            $filteredProject = $this->applyFilters($data['project'], $request);
            $data['project'] = $filteredProject;

            // Get categories for filter dropdown
            $data['categories'] = \Modules\Progress\Models\WorkItemCategory::orderBy('name')->get();
            
            // ✅ Get only subprojects that have items for filter dropdown
            $data['subprojects'] = $this->getSubprojectsWithItems($project);
            
            // Pass as_of_date to view
            $data['asOfDate'] = $asOfDate;

            return view('projects.progress', $data);

        } catch (\Exception $e) {
            Log::error('Error in project progress report', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'حدث خطأ أثناء تحميل التقرير. الرجاء المحاولة مرة أخرى.');
        }
    }

    /**
     * Calculate project progress data efficiently
     */
    protected function calculateProjectProgress(Project $project, string $fromDate, string $toDate, string $asOfDate = null): array
    {
        // Use as_of_date if provided, otherwise use today
        $asOfDateObj = $asOfDate ? Carbon::parse($asOfDate) : Carbon::today();
        $today = $asOfDateObj->format('Y-m-d');
        
        // Load project with necessary relationships in one query
        $project->load([
            'client',
            'items' => function ($query) {
                $query->with('workItem.category');
            }
        ]);

        // Get all progress data for project items in one efficient query
        $progressData = DB::table('daily_progress')
            ->select(
                'project_item_id',
                DB::raw("SUM(CASE WHEN progress_date < '{$today}' THEN quantity ELSE 0 END) as previous_progress"),
                DB::raw("SUM(CASE WHEN progress_date = '{$today}' THEN quantity ELSE 0 END) as current_progress"),
                DB::raw("SUM(CASE WHEN progress_date <= '{$toDate}' THEN quantity ELSE 0 END) as total_completed"),
                DB::raw("SUM(CASE WHEN progress_date BETWEEN '{$fromDate}' AND '{$toDate}' THEN quantity ELSE 0 END) as period_progress"),
                DB::raw("MIN(progress_date) as progress_start_date"),
                DB::raw("MAX(progress_date) as progress_end_date")
            )
            ->whereIn('project_item_id', $project->items->pluck('id'))
            ->whereNull('deleted_at')
            ->groupBy('project_item_id')
            ->get()
            ->keyBy('project_item_id');

        // Get project dates
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
        
        $fromDateObj = Carbon::parse($fromDate);
        $toDateObj = Carbon::parse($toDate);
        $todayObj = $asOfDateObj->copy();

        // Attach progress data and calculate all metrics for each item
        $project->items->each(function ($item) use ($project, $progressData, $projectStartDate, $projectEndDate, $fromDateObj, $toDateObj, $todayObj) {
            $progress = $progressData->get($item->id);

            // Progress quantities
            $item->previous_progress = $progress ? max((float)$progress->previous_progress, 0) : 0;
            $item->current_progress = $progress ? max((float)$progress->current_progress, 0) : 0;
            $item->period_progress = $progress ? max((float)$progress->period_progress, 0) : 0;
            // total_completed = previous + current (as shown in the table)
            $item->total_completed = $item->previous_progress + $item->current_progress;
            $item->remaining = $item->total_quantity - $item->total_completed; // يسمح بالسالب
            
            // Progress dates (first and last progress_date from daily_progress)
            $item->progress_start_date = $progress && $progress->progress_start_date 
                ? Carbon::parse($progress->progress_start_date)->format('Y-m-d') 
                : null;
            $item->progress_end_date = $progress && $progress->progress_end_date 
                ? Carbon::parse($progress->progress_end_date)->format('Y-m-d') 
                : null;

            // Item dates with fallback to project dates
            $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
            $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
            
            // Planned calculations
            // استخدام estimated_daily_qty من قاعدة البيانات فقط
            $totalItemDays = max($itemStartDate->diffInDays($itemEndDate) + 1, 1);
            $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;

            // Get weekly holidays from project (e.g., "5,6" for Friday and Saturday)
            $weeklyHolidays = $project->weekly_holidays 
                ? explode(',', $project->weekly_holidays) 
                : [];
            
            // Previous Planned = (عدد أيام العمل من بداية Item إلى أمس) × estimated_daily_qty
            $yesterdayObj = $todayObj->copy()->subDay();
            
            // إذا كان أمس قبل بداية المهمة، Previous Planned = 0
            if ($yesterdayObj->lt($itemStartDate)) {
                $item->planned_until_from_date = 0;
            } else {
                $workingDaysUntilYesterday = $this->calculateWorkingDays($itemStartDate, $yesterdayObj, $weeklyHolidays);
                $item->planned_until_from_date = $dailyPlannedQuantity > 0 
                    ? min($workingDaysUntilYesterday * $dailyPlannedQuantity, $item->total_quantity) 
                    : 0;
            }

            // Current Planned: استخدام estimated_daily_qty من قاعدة البيانات فقط
            // تحقق إذا كان اليوم يوم عمل أم عطلة، وإذا كان اليوم بعد start_date
            $isWorkingDay = !in_array($todayObj->dayOfWeek, $weeklyHolidays);
            $isAfterStartDate = $todayObj->gte($itemStartDate);
            $isBeforeEndDate = $todayObj->lte($itemEndDate);
            
            // Current Planned = 0 إذا كان اليوم إجازة أو قبل البداية أو بعد النهاية
            $item->planned_today = ($isWorkingDay && $isAfterStartDate && $isBeforeEndDate) 
                ? $dailyPlannedQuantity 
                : 0;

            // Completed Planned: حساب عدد أيام العمل حتى to_date
            // إذا كان to_date قبل بداية المهمة، Completed Planned = 0
            if ($toDateObj->lt($itemStartDate)) {
                $item->planned_total_quantity = 0;
            } else {
                $workingDaysUntilToDate = $this->calculateWorkingDays($itemStartDate, $toDateObj, $weeklyHolidays);
                $item->planned_total_quantity = $dailyPlannedQuantity > 0 
                    ? min($workingDaysUntilToDate * $dailyPlannedQuantity, $item->total_quantity) 
                    : 0;
            }

            // Remaining Planned: حساب الكمية المخططة المتبقية
            // Remaining Planned = Total Quantity - Planned Total Quantity (يسمح بالسالب)
            $item->remaining_planned = $item->total_quantity - $item->planned_total_quantity;

            // Percentages - حساب النسب المئوية بناءً على إجمالي الكمية (Total Quantity)
            
            // Previous Actual % = Previous Actual / Total Quantity
            $item->previous_progress_percentage = $item->total_quantity > 0
                ? round(($item->previous_progress / $item->total_quantity) * 100, 2)
                : 0;

            // Current Actual % = Current Actual / Total Quantity
            $item->current_progress_percentage = $item->total_quantity > 0
                ? round(($item->current_progress / $item->total_quantity) * 100, 2)
                : 0;

            // Completed Actual % = Total Completed / Total Quantity
            $item->total_completed_percentage = $item->total_quantity > 0
                ? round(($item->total_completed / $item->total_quantity) * 100, 2)
                : 0;

            // Remaining based on total quantity (يسمح بالسالب)
            $item->remaining_percentage = $item->total_quantity > 0
                ? round(($item->remaining / $item->total_quantity) * 100, 2)
                : 0;

            // Remaining Planned Percentage (يسمح بالسالب)
            $item->remaining_planned_percentage = $item->total_quantity > 0
                ? round(($item->remaining_planned / $item->total_quantity) * 100, 2)
                : 0;

            // Planned percentages based on total quantity
            $item->planned_until_from_date_percentage = $item->total_quantity > 0
                ? min(round(($item->planned_until_from_date / $item->total_quantity) * 100, 2), 100)
                : 0;

            $item->planned_today_percentage = $item->total_quantity > 0
                ? min(round(($item->planned_today / $item->total_quantity) * 100, 2), 100)
                : 0;

            $item->planned_total_percentage = $item->total_quantity > 0
                ? min(round(($item->planned_total_quantity / $item->total_quantity) * 100, 2), 100)
                : 0;

            // Performance comparison - Previous
            $item->previous_qty_class = match (true) {
                $item->previous_progress > $item->planned_until_from_date => 'above-expected',
                $item->previous_progress < $item->planned_until_from_date => 'below-expected',
                default => 'equal-expected',
            };

            $item->previous_qty_icon = match (true) {
                $item->previous_progress > $item->planned_until_from_date => 'fa-arrow-up',
                $item->previous_progress < $item->planned_until_from_date => 'fa-arrow-down',
                default => 'fa-equals',
            };

            $item->previous_qty_color = match (true) {
                $item->previous_progress > $item->planned_until_from_date => 'text-success',
                $item->previous_progress < $item->planned_until_from_date => 'text-danger',
                default => 'text-warning',
            };

            // Performance comparison - Current
            $item->current_qty_class = match (true) {
                $item->current_progress > $item->planned_today => 'above-expected',
                $item->current_progress < $item->planned_today => 'below-expected',
                default => 'equal-expected',
            };

            $item->current_qty_icon = match (true) {
                $item->current_progress > $item->planned_today => 'fa-arrow-up',
                $item->current_progress < $item->planned_today => 'fa-arrow-down',
                default => 'fa-equals',
            };

            $item->current_qty_color = match (true) {
                $item->current_progress > $item->planned_today => 'text-success',
                $item->current_progress < $item->planned_today => 'text-danger',
                default => 'text-warning',
            };

            // Performance comparison - Completed
            $item->completed_qty_class = match (true) {
                $item->total_completed > $item->planned_total_quantity => 'above-expected',
                $item->total_completed < $item->planned_total_quantity => 'below-expected',
                default => 'equal-expected',
            };

            $item->completed_qty_icon = match (true) {
                $item->total_completed > $item->planned_total_quantity => 'fa-arrow-up',
                $item->total_completed < $item->planned_total_quantity => 'fa-arrow-down',
                default => 'fa-equals',
            };

            $item->completed_qty_color = match (true) {
                $item->total_completed > $item->planned_total_quantity => 'text-success',
                $item->total_completed < $item->planned_total_quantity => 'text-danger',
                default => 'text-warning',
            };

            // Performance comparison - Remaining
            $item->remaining_qty_class = match (true) {
                $item->remaining < $item->remaining_planned => 'above-expected', // أقل متبقي = أفضل
                $item->remaining > $item->remaining_planned => 'below-expected', // أكثر متبقي = أسوأ
                default => 'equal-expected',
            };

            $item->remaining_qty_icon = match (true) {
                $item->remaining < $item->remaining_planned => 'fa-arrow-up',
                $item->remaining > $item->remaining_planned => 'fa-arrow-down',
                default => 'fa-equals',
            };

            $item->remaining_qty_color = match (true) {
                $item->remaining < $item->remaining_planned => 'text-success',
                $item->remaining > $item->remaining_planned => 'text-danger',
                default => 'text-warning',
            };
        });

        // Calculate overall project progress
        // ✅ يحسب فقط من البنود القابلة للقياس (is_measurable = true)
        $measurableItems = $project->items->filter(function ($item) {
            return $item->is_measurable ?? false;
        });
        
        $totalProjectQuantity = $measurableItems->sum('total_quantity');
        $totalCompletedQuantity = $measurableItems->sum('total_completed');
        $projectProgress = $totalProjectQuantity > 0
            ? min(round(($totalCompletedQuantity / $totalProjectQuantity) * 100, 2), 100)
            : 0;

        // Check if today is a holiday
        $weeklyHolidays = $project->weekly_holidays ? explode(',', $project->weekly_holidays) : [];
        $isTodayHoliday = in_array(Carbon::today()->dayOfWeek, $weeklyHolidays);

        return [
            'project' => $project,
            'projectProgress' => $projectProgress,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'projectStartDate' => $projectStartDate,
            'isTodayHoliday' => $isTodayHoliday,
            'projectEndDate' => $projectEndDate,
        ];
    }

    /**
     * عرض نموذج تعديل المشروع
     */
    public function edit(Project $project)
    {
        $project->load(['items.workItem', 'employees', 'subprojects']);

        $clients = Client::orderBy('cname')->get();
        $workItems = WorkItem::with('category')->orderBy('name')->get();
        $employees = $this->employeeRepository->getAll();
        $templates = ProjectTemplate::withCount('items')->orderBy('name')->get();
        $projectTypes = ProjectType::orderBy('name')->get();
        $categories = WorkItemCategory::with('workItems')->orderBy('name')->get();
        
        // Pass items and subprojects to the view
        $projectItems = $project->items;
        $subprojects = $project->subprojects;
        
        // 🔍 Debug: تأكد إن الـ IDs موجودة
        \Log::info('===== PROJECT EDIT =====');
        \Log::info('Project ID: ' . $project->id);
        \Log::info('Items to send to view:', [
            'count' => $projectItems->count(),
            'items' => $projectItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'work_item_id' => $item->work_item_id,
                    'work_item_name' => $item->workItem->name ?? 'N/A',
                    'total_quantity' => $item->total_quantity
                ];
            })->toArray()
        ]);

        return view('progress::projects.edit', compact(
            'project',
            'projectItems',
            'subprojects',
            'clients',
            'workItems',
            'employees',
            'templates',
            'projectTypes',
            'categories'
        ));
    }

    /**
     * تحديث المشروع
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        try {
            $validated = $request->validated();
            
            // 🔍 Debug: Log received items with IDs
            \Log::info('===== PROJECT UPDATE REQUEST =====');
            \Log::info('Project ID: ' . $project->id);
            \Log::info('Items received from form:', [
                'count' => count($validated['items'] ?? []),
                'items_summary' => array_map(function($item, $key) {
                    return [
                        'key' => $key,
                        'id' => $item['id'] ?? 'NEW',
                        'work_item_id' => $item['work_item_id'] ?? null,
                        'predecessor' => $item['predecessor'] ?? null
                    ];
                }, $validated['items'] ?? [], array_keys($validated['items'] ?? []))
            ]);
            
            // 🔍 Debug: Log received subprojects
            \Log::info('Subprojects received from form:', [
                'count' => count($validated['subprojects'] ?? []),
                'subprojects' => $validated['subprojects'] ?? []
            ]);
            
            $this->projectService->updateProject($project, $validated);

            // Clear progress report cache after updating project
            Cache::forget("project_progress_{$project->id}_*");
            // Clear all cache keys for this project (for any date range)
            $cacheKeys = Cache::get('project_progress_cache_keys', []);
            foreach ($cacheKeys as $key) {
                if (str_contains($key, "project_progress_{$project->id}_")) {
                    Cache::forget($key);
                }
            }

            return redirect()
                ->route('projects.show', $project)
                ->with('success', __('general.project_updated_successfully'));

        } catch (\Exception $e) {
            \Log::error('Project update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', __('general.error_updating_project') . ': ' . $e->getMessage());
        }
    }

    /**
     * حذف المشروع
     */
    public function destroy(Project $project)
    {
        try {
            $this->projectService->deleteProject($project);

            return redirect()
                ->route('projects.index')
                ->with('success', __('general.project_deleted_successfully'));

        } catch (\Exception $e) {
            return back()
                ->with('error', __('general.error_deleting_project') . ': ' . $e->getMessage());
        }
    }

    /**
     * نشر المسودة كمشروع فعلي
     */
    public function publish(Project $project)
    {
        $result = $this->projectService->publishDraft($project);

        if (!$result['success']) {
            return back()->with('error', implode('<br>', $result['errors']));
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', __('general.project_published_successfully'));
    }

    /**
     * نسخ المشروع
     */
    public function copy(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            $newProject = $this->projectService->copyProject($project, $request->name);

            // If AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('general.project_copied_successfully'),
                    'redirect_url' => route('projects.edit', $newProject)
                ]);
            }

            return redirect()
                ->route('projects.edit', $newProject)
                ->with('success', __('general.project_copied_successfully'));

        } catch (\Exception $e) {
            // If AJAX request, return JSON error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('general.error_copying_project') . ': ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', __('general.error_copying_project') . ': ' . $e->getMessage());
        }
    }

    /**
     * تصدير بيانات المشروع إلى Excel
     */
    public function export(Project $project, Request $request)
    {
        try {
            $toDate = $request->input('to_date', Carbon::today()->format('Y-m-d'));
            $fromDate = $request->input('from_date', Carbon::parse($toDate)->subWeek()->format('Y-m-d'));

            $project->load([
                'items.workItem',
                'items.dailyProgress' => function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('progress_date', [$fromDate, $toDate])
                        ->with('employee');
                }
            ]);

            $data = [];

            foreach ($project->items as $item) {
                $data[] = [
                    'رقم البند' => $item->id,
                    'اسم البند' => $item->workItem->name ?? '-',
                    'الكمية الكلية' => $item->total_quantity ?? 0,
                    'الكمية المنفذة' => $item->completed_quantity ?? 0,
                    'الكمية المتبقية' => $item->remaining_quantity ?? 0,
                    'نسبة الإنجاز' => $item->total_quantity > 0
                        ? round(($item->completed_quantity / $item->total_quantity) * 100, 2) . '%'
                        : '0%',
                    'تاريخ البداية' => $item->start_date,
                    'تاريخ النهاية' => $item->end_date,
                ];

                foreach ($item->dailyProgress as $progress) {
                    $data[] = [
                        'التاريخ' => $progress->progress_date,
                        'الموظف' => $progress->employee->name ?? '-',
                        'الكمية المنفذة' => $progress->quantity,
                        'ملاحظات' => $progress->notes ?? '-',
                    ];
                }
            }

            $fileName = 'project_' . $project->id . '_' . Carbon::now()->format('Y-m-d') . '.xlsx';

            return (new FastExcel(collect($data)))->download($fileName);

        } catch (\Exception $e) {
            return back()->with('error', __('general.error_exporting') . ': ' . $e->getMessage());
        }
    }

    /**
     * الحصول على تفاصيل قالب المشروع
     */
    public function getTemplate($templateId)
    {
        try {
            $template = ProjectTemplate::with('items.workItem')->findOrFail($templateId);

            return response()->json([
                'success' => true,
                'template' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * الحصول على تفاصيل بند العمل
     */
    public function getWorkItem($workItemId)
    {
        try {
            $workItem = WorkItem::findOrFail($workItemId);

            return response()->json([
                'success' => true,
                'workItem' => $workItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * جلب المشاريع الفرعية مع نسبة الإنجاز
     * ✅ Returns only subprojects that have items
     */
    public function getSubprojects(Project $project)
    {
        try {
            // Load project with necessary relationships (including trashed work items)
            $project->load([
                'items' => function ($query) {
                    $query->with(['workItem' => function ($q) {
                        $q->withTrashed();
                    }]);
                },
                'subprojects'
            ]);
            
            // ✅ Get only subprojects that have items (includes virtual "بدون فرعي" subproject)
            $subprojects = $this->getSubprojectsWithItems($project)->map(function ($subproject) use ($project) {
                // Check if this is the virtual "بدون فرعي" subproject
                $withoutSubprojectName = __('general.without_subproject');
                $isWithoutSubproject = ($subproject->name === $withoutSubprojectName && ($subproject->id === null || !isset($subproject->id)));
                
                // Get all items for this subproject
                if ($isWithoutSubproject) {
                    // Get items without subproject (where subproject_name is null or empty)
                    $allItems = $project->items->filter(function ($item) {
                        return empty($item->subproject_name);
                    })->values();
                } else {
                    // Get items for regular subprojects
                    $allItems = $project->items->where('subproject_name', $subproject->name)->values();
                }
                
                // Filter only measurable items for progress calculation
                $measurableItems = $allItems->filter(function ($item) {
                    return $item->is_measurable ?? false;
                });
                
                // Calculate progress only for measurable items
                $totalQuantity = $measurableItems->sum('total_quantity');
                $completedQuantity = $measurableItems->sum('completed_quantity');
                $progress = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100, 2) : 0;
                
                // Calculate progress with items capped at 100% (if item ratio > 100%, use 100%)
                $totalQuantityUnder100 = $measurableItems->sum('total_quantity');
                $completedQuantityUnder100 = $measurableItems->sum(function ($item) {
                    $itemProgress = ($item->total_quantity ?? 0) > 0 
                        ? (($item->completed_quantity ?? 0) / $item->total_quantity) * 100 
                        : 0;
                    // If progress > 100%, cap completed quantity at total_quantity (which equals 100%)
                    if ($itemProgress > 100) {
                        return $item->total_quantity ?? 0;
                    }
                    return $item->completed_quantity ?? 0;
                });
                
                $progressUnder100 = $totalQuantityUnder100 > 0 
                    ? round(($completedQuantityUnder100 / $totalQuantityUnder100) * 100, 2) 
                    : 0;
                
                // Cap progress at 100% for subproject level calculation
                $progressCapped = $progress > 100 ? 100 : $progress;
                $progressUnder100Capped = $progressUnder100 > 100 ? 100 : $progressUnder100;
                
                // Calculate planned progress based on planned_total_quantity for measurable items only
                $todayObj = Carbon::today();
                $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : Carbon::today();
                $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : Carbon::today()->addMonth();
                $weeklyHolidays = $project->weekly_holidays 
                    ? explode(',', $project->weekly_holidays) 
                    : [];
                
                $plannedTotalQuantity = 0;
                foreach ($measurableItems as $item) {
                    $itemStartDate = $item->start_date ? Carbon::parse($item->start_date) : $projectStartDate;
                    $itemEndDate = $item->end_date ? Carbon::parse($item->end_date) : $projectEndDate;
                    $dailyPlannedQuantity = $item->estimated_daily_qty ?? 0;
                    
                    // Calculate planned_total_quantity until today
                    if ($todayObj->lt($itemStartDate)) {
                        $plannedTotalQuantity += 0;
                    } else {
                        $workingDaysUntilToday = $this->calculateWorkingDays($itemStartDate, $todayObj, $weeklyHolidays);
                        $itemPlannedTotal = $dailyPlannedQuantity > 0 
                            ? min($workingDaysUntilToday * $dailyPlannedQuantity, $item->total_quantity) 
                            : 0;
                        $plannedTotalQuantity += $itemPlannedTotal;
                    }
                }
                
                // Calculate planned progress percentage (planned_total_quantity / total_quantity) * 100
                $plannedProgress = $totalQuantity > 0 
                    ? min(100, round(($plannedTotalQuantity / $totalQuantity) * 100, 2)) 
                    : 0;
                
                // No weight calculation - return quantities directly
                $weight = $subproject->weight ?? 0;
                
                // Prepare items list with measurable flag
                $itemsList = $allItems->map(function ($item) {
                    try {
                        $workItem = $item->workItem;
                        return [
                            'id' => $item->id,
                            'name' => $workItem ? $workItem->name : ($item->item_label ?? 'Unknown Item'),
                            'is_measurable' => $item->is_measurable ?? false,
                            'total_quantity' => $item->total_quantity ?? 0,
                            'completed_quantity' => $item->completed_quantity ?? 0,
                            'unit' => $workItem ? ($workItem->unit ?? '') : '',
                            'progress' => ($item->total_quantity ?? 0) > 0 
                                ? round((($item->completed_quantity ?? 0) / $item->total_quantity) * 100, 2) 
                                : 0,
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Error processing item in subproject', [
                            'item_id' => $item->id ?? null,
                            'error' => $e->getMessage()
                        ]);
                        return null;
                    }
                })->filter()->values();
                
                return [
                    'id' => $subproject->id ?? null,
                    'name' => $subproject->name ?? __('general.without_subproject'),
                    'weight' => $weight,
                    'progress' => $progress,
                    'progress_under_100' => $progressUnder100,
                    'planned_progress' => $plannedProgress,
                    'total_quantity' => $totalQuantity,
                    'completed_quantity' => $completedQuantity,
                    'completed_quantity_under_100' => $completedQuantityUnder100,
                    'planned_total_quantity' => $plannedTotalQuantity,
                    'start_date' => $subproject->start_date?->format('Y-m-d'),
                    'end_date' => $subproject->end_date?->format('Y-m-d'),
                    'items' => $itemsList,
                ];
            })->values(); // Re-index array after filtering

            // Return subprojects directly (not wrapped in success object) for compatibility
            return response()->json($subprojects);
        } catch (\Exception $e) {
            \Log::error('Error loading subprojects', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Error loading subprojects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض Gantt Chart للمشروع
     */
    public function ganttChart(Project $project)
    {
        $this->authorize('view', $project);
        
        $project->load(['client', 'type']);
        
        return view('progress::projects.gantt', compact('project'));
    }

    /**
     * الحصول على بيانات Gantt Chart
     */
    public function ganttData(Project $project)
    {
        $this->authorize('view', $project);

        try {
            // جلب items مع العلاقات المطلوبة
            $items = $project->items()
                ->with([
                    'workItem.category',
                    'predecessorItem.workItem'
                ])
                ->orderBy('item_order')
                ->get();

            // حساب الكميات المكتملة لكل items في query واحد
            $completedQuantities = DB::table('daily_progress')
                ->select('project_item_id', DB::raw('SUM(quantity) as completed'))
                ->whereIn('project_item_id', $items->pluck('id'))
                ->whereNull('deleted_at')
                ->groupBy('project_item_id')
                ->pluck('completed', 'project_item_id');

            // تحويل items إلى تنسيق Gantt Chart
            $tasks = $items->map(function ($item) use ($project, $completedQuantities) {
                // حساب التقدم المكتمل
                $completedQuantity = $completedQuantities->get($item->id, 0);
                $progressPercentage = $item->total_quantity > 0 
                    ? min(round(($completedQuantity / $item->total_quantity) * 100, 2), 100)
                    : 0;

                // تحديد الحالة
                $status = $this->calculateItemStatus($item, $progressPercentage);

                // معلومات البند السابق
                $predecessorInfo = null;
                if ($item->predecessor && $item->predecessorItem) {
                    $predecessorInfo = [
                        'id' => $item->predecessorItem->id,
                        'name' => $item->predecessorItem->workItem->name ?? 'N/A',
                        'type' => $item->dependency_type ?? 'FS', // Finish-to-Start default
                        'lag' => $item->lag ?? 0,
                    ];
                }

                return [
                    'id' => $item->id,
                    'name' => $item->workItem->name ?? 'N/A',
                    'description' => $item->notes,
                    'start_date' => $item->start_date ?? $project->start_date,
                    'end_date' => $item->end_date ?? $project->end_date,
                    'duration' => $item->duration ?? 0,
                    'total_quantity' => (float) $item->total_quantity,
                    'completed_quantity' => (float) $completedQuantity,
                    'progress' => $progressPercentage,
                    'status' => $status,
                    'unit' => strtoupper($item->workItem->unit ?? ''),
                    'subproject' => $item->subproject_name,
                    'predecessor' => $predecessorInfo,
                    'work_item' => [
                        'id' => $item->workItem->id ?? null,
                        'category' => $item->workItem->category->name ?? null,
                        'notes' => $item->workItem->description ?? null,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'status' => $project->status,
                ],
                'tasks' => $tasks,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading Gantt data', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل بيانات Gantt Chart',
            ], 500);
        }
    }

    /**
     * حساب حالة البند بناءً على التقدم والتواريخ
     */
    private function calculateItemStatus($item, $progressPercentage): string
    {
        $today = Carbon::today();
        $endDate = $item->end_date ? Carbon::parse($item->end_date) : null;
        $startDate = $item->start_date ? Carbon::parse($item->start_date) : null;

        // مكتمل
        if ($progressPercentage >= 100) {
            return 'completed';
        }

        // لم يبدأ بعد
        if ($startDate && $today->lt($startDate)) {
            return 'pending';
        }

        // متأخر (تجاوز تاريخ النهاية وغير مكتمل)
        if ($endDate && $today->gt($endDate) && $progressPercentage < 100) {
            return 'delayed';
        }

        // قيد التنفيذ
        if ($progressPercentage > 0 && $progressPercentage < 100) {
            return 'in_progress';
        }

        // الحالة الافتراضية
        return 'pending';
    }

    /**
     * حساب عدد أيام العمل الفعلية بين تاريخين (مع استثناء العطلات الأسبوعية)
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $weeklyHolidays (e.g., [5, 6] for Friday and Saturday)
     * @return int
     */
    private function calculateWorkingDays($startDate, $endDate, array $weeklyHolidays = []): int
    {
        if ($startDate->gt($endDate)) {
            return 0;
        }

        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // إذا كان اليوم ليس ضمن العطلات الأسبوعية، احسبه كيوم عمل
            if (!in_array($currentDate->dayOfWeek, $weeklyHolidays)) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * تطبيق الفلاتر على عناصر المشروع
     */
    private function applyFilters($project, $request)
    {
        $items = $project->items;

        // Filter by category
        if ($request->filled('category_id')) {
            $items = $items->filter(function ($item) use ($request) {
                return $item->workItem && $item->workItem->category_id == $request->category_id;
            });
        }

        // Filter by subproject
        if ($request->filled('subproject_name')) {
            $subprojectName = $request->subproject_name;
            $items = $items->filter(function ($item) use ($subprojectName) {
                if ($subprojectName === 'null') {
                    return empty($item->subproject_name);
                }
                return $item->subproject_name == $subprojectName;
            });
        }

        // Filter by start date from
        if ($request->filled('start_date_from')) {
            $startDateFrom = Carbon::parse($request->start_date_from);
            $items = $items->filter(function ($item) use ($startDateFrom) {
                if (!$item->start_date) return false;
                return Carbon::parse($item->start_date)->gte($startDateFrom);
            });
        }

        // Filter by start date to
        if ($request->filled('start_date_to')) {
            $startDateTo = Carbon::parse($request->start_date_to);
            $items = $items->filter(function ($item) use ($startDateTo) {
                if (!$item->start_date) return false;
                return Carbon::parse($item->start_date)->lte($startDateTo);
            });
        }

        // Filter by end date from
        if ($request->filled('end_date_from')) {
            $endDateFrom = Carbon::parse($request->end_date_from);
            $items = $items->filter(function ($item) use ($endDateFrom) {
                $endDate = $item->end_date ?? $item->planned_end_date;
                if (!$endDate) return false;
                return Carbon::parse($endDate)->gte($endDateFrom);
            });
        }

        // Filter by end date to
        if ($request->filled('end_date_to')) {
            $endDateTo = Carbon::parse($request->end_date_to);
            $items = $items->filter(function ($item) use ($endDateTo) {
                $endDate = $item->end_date ?? $item->planned_end_date;
                if (!$endDate) return false;
                return Carbon::parse($endDate)->lte($endDateTo);
            });
        }

        // Filter by remaining performance
        if ($request->filled('remaining_performance')) {
            $remainingPerformanceFilter = $request->remaining_performance;
            $items = $items->filter(function ($item) use ($remainingPerformanceFilter) {
                // أقل متبقي من المتوقع = أفضل (above)
                // أكثر متبقي من المتوقع = أسوأ (below)
                $remainingStatus = match (true) {
                    $item->remaining < $item->remaining_planned => 'above', // أقل متبقي = أفضل
                    $item->remaining > $item->remaining_planned => 'below', // أكثر متبقي = أسوأ
                    default => 'equal',
                };
                return $remainingStatus === $remainingPerformanceFilter;
            });
        }

        // Filter by completed status
        if ($request->filled('completed_status')) {
            $completedStatusFilter = $request->completed_status;
            $items = $items->filter(function ($item) use ($completedStatusFilter) {
                // Completed = previous_progress + current_progress (as shown in the table)
                $previousProgress = (float)($item->previous_progress ?? 0);
                $currentProgress = (float)($item->current_progress ?? 0);
                $totalCompleted = $previousProgress + $currentProgress;
                
                $totalQuantity = (float)($item->total_quantity ?? 0);
                
                // Handle edge cases
                if ($totalQuantity == 0) {
                    // If no quantity, consider it not started
                    return $completedStatusFilter === 'not_started';
                }
                
                // Calculate completion status based on previous + current
                $isCompleted = $totalCompleted >= $totalQuantity;
                $isInProgress = $totalCompleted > 0 && $totalCompleted < $totalQuantity;
                $isNotStarted = $totalCompleted <= 0;
                
                return match ($completedStatusFilter) {
                    'completed' => $isCompleted,
                    'in_progress' => $isInProgress,
                    'not_started' => $isNotStarted,
                    default => true,
                };
            });
        }

        // Filter by search (item name)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $items = $items->filter(function ($item) use ($search) {
                return $item->workItem && str_contains(strtolower($item->workItem->name), $search);
            });
        }

        // Update the project items with filtered collection
        $project->setRelation('items', $items->values());

        return $project;
    }

    /**
     * تحديث وزن المشروع الفرعي
     */
    public function updateSubprojectWeight(Request $request, Project $project, $subprojectId)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        $subproject = $project->subprojects()->findOrFail($subprojectId);
        $subproject->weight = $request->weight;
        $subproject->save();

        // Recalculate weighted progress
        $items = $subproject->projectItems;
        $totalQuantity = $items->sum('total_quantity');
        $completedQuantity = $items->sum('completed_quantity');
        $progress = $totalQuantity > 0 ? round(($completedQuantity / $totalQuantity) * 100, 2) : 0;
        $weightedProgress = $progress * ($request->weight / 100);

        return response()->json([
            'success' => true,
            'message' => __('general.weight_updated_successfully'),
            'subproject' => [
                'id' => $subproject->id,
                'name' => $subproject->name,
                'weight' => $subproject->weight,
                'progress' => $progress,
                'weighted_progress' => round($weightedProgress, 2),
            ],
        ]);
    }

    /**
     * ✅ تحديث وزن جميع المشاريع الفرعية دفعة واحدة
     */
    public function updateAllSubprojectsWeight(Request $request, Project $project)
    {
        $request->validate([
            'weights' => 'required|array',
            'weights.*' => 'required|numeric|min:0|max:100',
        ]);

        $weights = $request->weights;
        $subprojects = $this->getSubprojectsWithItems($project);
        $updatedCount = 0;
        $totalWeight = 0;

        // Calculate total weight
        foreach ($weights as $weight) {
            $totalWeight += $weight;
        }

        // Validate total equals 100
        if (abs($totalWeight - 100) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => __('general.total_weights_must_equal_100') . ': ' . number_format($totalWeight, 2) . '%',
            ], 422);
        }

        // Update each subproject weight
        foreach ($subprojects as $subproject) {
            if (isset($weights[$subproject->id])) {
                $subproject->weight = $weights[$subproject->id];
                $subproject->save();
                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('general.all_weights_updated_successfully') . ' (' . $updatedCount . ' ' . __('general.subprojects') . ')',
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * ✅ Get only subprojects that have items
     * Helper method to filter subprojects with items
     */
    private function getSubprojectsWithItems(Project $project)
    {
        // Get unique subproject names from items
        $subprojectNamesWithItems = $project->items()
            ->whereNotNull('subproject_name')
            ->distinct()
            ->pluck('subproject_name')
            ->toArray();
        
        // Filter subprojects to include only those with items
        $subprojects = $project->subprojects->filter(function ($subproject) use ($subprojectNamesWithItems) {
            return in_array($subproject->name, $subprojectNamesWithItems);
        });
        
        // Check if there are items without subproject
        $itemsWithoutSubproject = $project->items()->whereNull('subproject_name')->exists();
        
        // Add virtual "بدون فرعي" subproject if there are items without subproject
        if ($itemsWithoutSubproject) {
            $withoutSubprojectName = __('general.without_subproject');
            
            // Create a virtual Subproject object for "بدون فرعي"
            $virtualSubproject = new \Modules\Progress\Models\Subproject([
                'id' => null,
                'project_id' => $project->id,
                'name' => $withoutSubprojectName,
                'start_date' => null,
                'end_date' => null,
                'total_quantity' => 0,
                'unit' => null,
                'description' => null,
                'weight' => 0,
            ]);
            
            // Add virtual subproject to the collection
            $subprojects->push($virtualSubproject);
        }
        
        return $subprojects;
    }

    /**
     * Update item status for a project item
     */
    public function updateItemStatus(Request $request, Project $project, ProjectItem $projectItem)
    {
        $request->validate([
            'item_status_id' => 'nullable|exists:item_statuses,id'
        ]);

        // Ensure the item belongs to this project
        if ($projectItem->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => __('general.item_not_belongs_to_project')
            ], 403);
        }

        $projectItem->update([
            'item_status_id' => $request->item_status_id ?: null
        ]);

        // Load the item status relationship
        $projectItem->load('itemStatus');

        return response()->json([
            'success' => true,
            'message' => __('general.item_status_updated_successfully'),
            'item' => [
                'id' => $projectItem->id,
                'item_status_id' => $projectItem->item_status_id,
                'item_status' => $projectItem->itemStatus ? [
                    'id' => $projectItem->itemStatus->id,
                    'name' => $projectItem->itemStatus->name,
                    'color' => $projectItem->itemStatus->color,
                    'icon' => $projectItem->itemStatus->icon,
                ] : null
            ]
        ]);
    }
}
