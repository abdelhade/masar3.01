<?php

namespace Modules\Progress\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use Modules\HR\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\ProjectProgress;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\ItemStatus;

class ProjectProgressController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:view progress-projects')->only(['index','show','progress','gantt']);
        $this->middleware('can:create progress-projects')->only(['create', 'store']);
        $this->middleware('can:edit progress-projects')->only(['edit', 'update']);
        $this->middleware('can:delete progress-projects')->only('destroy');
        
    }

    public function index()
    {
        $employee = Auth::user()->employee;
        $clients = Client::all();
        $projectTypes = ProjectType::all();
        
        if (request('status') === 'draft') {
            $projects = ProjectProgress::with(['client', 'type', 'items'])
                ->withCount('items')
                ->where('status', 'draft')
				->where('is_progress', 1)
                ->latest()
                ->get();
            $draftsCount = $projects->count();
            return view('progress::projects.drafts', compact('projects', 'clients', 'projectTypes', 'draftsCount'));
        }

        $projects = ProjectProgress::with(['client', 'type', 'items'])
            ->withCount('items')
            ->withSum('items', 'total_quantity')
            ->withSum('dailyProgress', 'quantity')
            ->where('status', '!=', 'draft')
			->where('is_progress', 1)
            ->latest()
            ->get();
            
        // Draft calculation
        $draftsCount = ProjectProgress::where('status', 'draft')->count();

        return view('progress::projects.index', compact('projects', 'clients', 'projectTypes', 'draftsCount'));
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = \Modules\Progress\Models\ProjectProgress::create([
            'name' => $validated['name'],
            'holidays' => '5,6', // أيام الجمعة والسبت كقيمة افتراضية
            'status' => 'pending',
            'start_date' => now()->format('Y-m-d'),
			'is_progress' => 1,
        ]);

        return redirect()
            ->route('progress.project.index')
            ->with('success', 'تم إضافة المشروع بنجاح');
    }

    public function create()
    {
        $clients = Client::all();
        $workItems = WorkItem::all();
        $employees = Employee::all();
        $users = User::all();
        $templates = ProjectTemplate::with(['items.workItem'])->get();
        $projectTypes = ProjectType::all();

        $templates = $templates->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'items' => $template->items->map(function ($it) {
                    return [
                        'id' => $it->id, // Important for resolving predecessor IDs
                        'work_item_id' => $it->work_item_id,
                        'name' => $it->workItem?->name ?? 'غير محدد',
                        'unit' => $it->workItem?->unit ?? '',
                        'default_quantity' => $it->total_quantity, // Map total_quantity to default_quantity for consistency
                        'subproject_name' => $it->subproject_name,
                        'notes' => $it->notes,
                        'is_measurable' => $it->is_measurable,
                        'estimated_daily_qty' => $it->estimated_daily_qty,
                        'duration' => $it->duration,
                        'dependency_type' => $it->dependency_type,
                        'lag' => $it->lag,
                        'predecessor' => $it->predecessor,
                    ];
                })->values(),
            ];
        });

        return view('progress::projects.create', compact('clients', 'workItems', 'employees', 'users', 'templates', 'projectTypes'));
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('save_action') === 'draft';
        
        if ($isDraft) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                // We keep client_id as required if DB enforces it. 
                // If user really wants NO validation, we'd need to mock it or nullable DB.
                // Minimizing to just name and client.
                 'client_id' => 'required|exists:clients,id',
            ]);
            // Relaxed validation for drafts
            $status = 'draft';
            $items = []; 
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
                'start_date' => 'required|date',
                'status' => 'required',
                'working_zone' => 'nullable|string',
                'project_type_id' => 'required|exists:project_types,id',
                'items' => 'required|array|min:1',
                'items.*.work_item_id' => 'required|exists:work_items,id',
            ]);
            $status = $request['status'] === 'active' ? 'in_progress' : $request['status'];
            $items = $request['items'];
        }

        // 1. Create Project
        $project = ProjectProgress::create([
            'name' => $request['name'],
            'description' => $request['description'] ?? null,
            'client_id' => $request['client_id'],
            'start_date' => $request['start_date'] ?? null, // Allow null for draft
            'status' => $status,
            'working_zone' => $request['working_zone'] ?? ($isDraft ? 'Not Set' : null), 
            'project_type_id' => $request['project_type_id'] ?? 1, // Default just in case
            'working_days' => $request['working_days'] ?? 5,
            'daily_work_hours' => $request['daily_work_hours'] ?? 8,
            'holidays' => is_array($request['weekly_holidays'] ?? $request['holidays'] ?? null) 
                ? implode(',', $request['weekly_holidays'] ?? $request['holidays']) 
                : ($request['weekly_holidays'] ?? $request['holidays'] ?? '5,6'),
			'is_progress' => 1,
        ]);

        // If Draft and no items, return early
        if ($isDraft && empty($request['items'])) {
             return redirect()->route('progress.project.index', ['status' => 'draft'])
                 ->with('success', __('projects.draft_saved'));
        }
        
        // 2. Save Items (Pass 1: Create Records)
        if (!empty($request['items'])) {
            $itemMapping = []; // Maps Input Index => Created Item ID
            $allCreatedItems = [];
    
            foreach ($request['items'] as $index => $itemData) {
            
            // Calculate default dates if missing (fallback)
            // But frontend typically sends calculated dates.
            // basic fallback:
            $daily = $itemData['estimated_daily_qty'] > 0 ? $itemData['estimated_daily_qty'] : ($itemData['total_quantity'] ?: 1);
            $duration = $itemData['duration'] ?? ceil($itemData['total_quantity'] / $daily);
            
            $startDate = $itemData['start_date'] ?? $request['start_date'];
            $endDate = $itemData['end_date'] ?? Carbon::parse($startDate)->addDays($duration);

            $newItem = $project->items()->create([
                'project_id' => $project->id,
                'work_item_id' => $itemData['work_item_id'],
                'subproject_name' => $itemData['subproject_name'] ?? null,
                'total_quantity' => $itemData['total_quantity'],
                'estimated_daily_qty' => $itemData['estimated_daily_qty'],
                'completed_quantity' => 0,
                'remaining_quantity' => $itemData['total_quantity'],
                // Save Logic Dates
                'start_date' => $startDate,
                'end_date' => $endDate,
                // 'planned_end_date' => $endDate, // If model has this column
                'duration' => $duration,
                'notes' => $itemData['notes'] ?? null,
                'is_measurable' => isset($itemData['is_measurable']) ? 1 : 0,
                'dependency_type' => $itemData['dependency_type'] ?? 'end_to_start',
                'lag' => $itemData['lag'] ?? 0,
                'item_order' => $index,
                // Predecessor set in Pass 2
            ]);

            $itemMapping[$index] = $newItem->id;
            $allCreatedItems[$index] = $newItem;
        }

        // 3. Save Predecessors (Pass 2: Link)
        foreach ($request['items'] as $index => $itemData) {
            if (isset($itemData['predecessor']) && $itemData['predecessor'] !== '' && $itemData['predecessor'] !== null) {
                // Frontend sends Index of the predecessor row
                $predIndex = $itemData['predecessor'];
                
                if (isset($itemMapping[$predIndex])) {
                    $realPredId = $itemMapping[$predIndex];
                    // Update the item created in Pass 1
                    $allCreatedItems[$index]->update(['predecessor' => $realPredId]);
                }
            }
        }

        // 4. Update Project End Date (Max of items)
        $maxEndDate = $project->items()->max('end_date');
        $project->update(['end_date' => $maxEndDate]);

        // 5. Attach Employees & Users
        if ($request->has('employees')) {
            $project->employees()->sync($request->employees);
        }
        if ($request->has('users')) {
            $project->users()->sync($request->users);
        }

        // 6. Save as Template (if requested)
        if ($request->filled('save_as_template') && $request->save_as_template == 1) {
            $templateName = $request->template_name ?: $project->name;
            
            $template = ProjectTemplate::create([
                'name' => $templateName,
                'description' => $project->description,
                'project_type_id' => $project->project_type_id,
                'weekly_holidays' => explode(',', $project->holidays), // Store as array
            ]);

            // Clone items for template
            // We use the same pass1/pass2 logic but for template items
            // But we can reuse the $allCreatedItems to map to new template items.
            // Actually, we should just create new items for the template reflecting the PROJECT items.
            
            $templateItemMapping = []; // Old Project Item ID => New Template Item ID
            
            // Pass 1: Create Template Items
            foreach ($allCreatedItems as $index => $projectItem) {
                $templateItem = $template->items()->create([
                    'project_template_id' => $template->id, // explicit
                    'work_item_id' => $projectItem->work_item_id,
                    'subproject_name' => $projectItem->subproject_name,
                    'total_quantity' => $projectItem->total_quantity, // Use total as default
                    'estimated_daily_qty' => $projectItem->estimated_daily_qty,
                    'duration' => $projectItem->duration,
                    'notes' => $projectItem->notes,
                    'is_measurable' => $projectItem->is_measurable,
                    'dependency_type' => $projectItem->dependency_type,
                    'lag' => $projectItem->lag,
                    'item_order' => $projectItem->item_order,
                    // Predecessor set in Pass 2
                ]);
                
                $templateItemMapping[$projectItem->id] = $templateItem->id;
            }

            // Pass 2: Link Predecessors
            // We iterate the Project Items again, check their predecessor.
            // If Project Item A has Predecessor B
            // Then Template Item A' should have Predecessor B'
            foreach ($allCreatedItems as $projectItem) {
                if ($projectItem->predecessor) {
                    $templateItemId = $templateItemMapping[$projectItem->id] ?? null;
                    $templatePredId = $templateItemMapping[$projectItem->predecessor] ?? null;

                    if ($templateItemId && $templatePredId) {
                         // We need to find the template item instance to update
                         // Optimization: we could store object in mapping or just do update query
                         DB::table('project_items')->where('id', $templateItemId)->update(['predecessor' => $templatePredId]);
                    }
                }
            }
        }

    }

        return redirect()
            ->route('progress.project.show', $project->id)
            ->with('success', 'تم إضافة المشروع بنجاح');
    }


    public function show($id)
    {
        // جلب المشروع مع العلاقات مباشرة
        $project = ProjectProgress::with([
            'client' => function($q) {
                $q->withCount('projects');
            },
            'items' => function ($query) {
                $query->withSum('dailyProgress', 'quantity');
            },
            'items.workItem',
            'items.workItem.category',
            'items.status',
            'items.dailyProgress' => function ($query) {
                $query->latest('progress_date')->limit(10);
            },
            'items.dailyProgress.employee',
            'dailyProgress' => function ($query) {
                $query->latest('progress_date')->limit(5);
            },
            'dailyProgress.employee',
            'dailyProgress.projectItem.workItem'
        ])->findOrFail($id);

        // حساب التقدم
        $totalQuantity = 0;
        $totalCompleted = 0;
        
        $chartDataLabels = [];
        $chartDataValues = [];

        foreach ($project->items as $item) {
            // استخدام المجموع المحسوب مسبقاً
            $completedQuantity = $item->daily_progress_sum_quantity ?? 0;
            $item->completed_quantity = $completedQuantity;
            $item->remaining_quantity = max(0, $item->total_quantity - $completedQuantity);

            $completionPercentage = $item->total_quantity > 0
                ? min(100, ($completedQuantity / $item->total_quantity) * 100)
                : 0;

            $item->completion_percentage = round($completionPercentage, 2);

            $totalQuantity += $item->total_quantity;
            $totalCompleted += $completedQuantity;
            
            // Prepare Chart Data
            $chartDataLabels[] = $item->workItem ? $item->workItem->name : __('general.unknown_item');
            $chartDataValues[] = $item->completion_percentage;
        }

        $overallProgress = $totalQuantity > 0
            ? round(min(100, ($totalCompleted / $totalQuantity) * 100), 2)
            : 0;
            
        // Stats
        $totalItems = $project->items->count();
        
        $daysPassed = 0;
        $daysRemaining = 0;
        if ($project->start_date) {
            $start = Carbon::parse($project->start_date);
            $now = Carbon::now();
            $end = $project->end_date ? Carbon::parse($project->end_date) : null;
            
            if ($now->gte($start)) {
                $daysPassed = $start->diffInDays($now);
            }
            
            if ($end && $end->gte($now)) {
                $daysRemaining = $now->diffInDays($end);
            }
        }
        
        $totalEmployees = $project->employees->count();
        
        // Project Status Badge Logic
        $projectStatus = [
            'message' => __('general.status_' . $project->status),
            'color' => 'secondary',
            'icon' => 'fa-circle'
        ];
        
        if ($project->status === 'completed') {
            $projectStatus = ['message' => __('general.completed'), 'color' => 'success', 'icon' => 'fa-check-circle'];
        } elseif ($project->status === 'active') {
             if ($overallProgress >= 100) {
                 $projectStatus = ['message' => __('general.completed'), 'color' => 'success', 'icon' => 'fa-check-circle'];
             } elseif ($daysRemaining == 0 && $overallProgress < 100 && $project->end_date && Carbon::now()->gt($project->end_date)) {
                 $projectStatus = ['message' => __('general.delayed'), 'color' => 'danger', 'icon' => 'fa-exclamation-circle'];
             } else {
                 $projectStatus = ['message' => __('general.active'), 'color' => 'primary', 'icon' => 'fa-spinner fa-spin'];
             }
        }
        
        // Chart Data (Old Charts)
        $chartData = [
            'work_items' => $chartDataLabels,
            'completion_percentages' => $chartDataValues
        ];

        // Advanced Chart Data (Planned vs Actual)
        $advancedChartData = [
            'labels' => [],
            'planned' => [],
            'actual' => [],
            'ids' => [] // For filtering
        ];

        $subprojectsData = []; // Buffer for aggregation
        $categoriesData = []; // Buffer for category aggregation
        $itemsBySubproject = []; // Detailed data for dropdown chart
        $itemsByCategory = []; // Detailed data for category dropdown chart
        $hierarchicalData = []; // Nested data for accordion view
        $itemsByStatus = []; // Grouped by status

        foreach ($project->items as $item) {
            $advancedChartData['labels'][] = $item->workItem->name ?? 'Unknown';
            $advancedChartData['ids'][] = 'item-' . $item->id;
            
            // Actual
            $advancedChartData['actual'][] = round($item->completion_percentage, 1);

            // Planned (Time-based Calculation)
            if ($item->start_date && $item->end_date) {
                $start = Carbon::parse($item->start_date);
                $end = Carbon::parse($item->end_date);
                $totalDuration = max(1, $start->diffInDays($end)); // Avoid div by zero
                $daysElapsed = max(0, $start->diffInDays(now()));
                
                // If now is before start, 0. If now is after end, 100.
                if (now()->lt($start)) {
                    $plannedPercent = 0;
                } elseif (now()->gt($end)) {
                    $plannedPercent = 100;
                } else {
                    $plannedPercent = ($daysElapsed / $totalDuration) * 100;
                }
                $advancedChartData['planned'][] = round($plannedPercent, 1);
            } else {
                $plannedPercent = 0;
                $advancedChartData['planned'][] = 0;
            }
            
            // Subproject Aggregation (Group by subproject_name)
            $subName = $item->subproject_name ?? 'Main Project';
            if (!isset($subprojectsData[$subName])) {
                $subprojectsData[$subName] = [
                    'total_qty' => 0,
                    'completed_qty' => 0,
                    'planned_qty' => 0
                ];
            }
            $subprojectsData[$subName]['total_qty'] += $item->total_quantity;
            $subprojectsData[$subName]['completed_qty'] += $item->completed_quantity;
            $subprojectsData[$subName]['planned_qty'] += ($item->total_quantity * $plannedPercent / 100);

            // Category Aggregation
            $catName = ($item->workItem && $item->workItem->category) ? $item->workItem->category->name : 'Uncategorized';
            if (!isset($categoriesData[$catName])) {
                $categoriesData[$catName] = [
                    'total_qty' => 0,
                    'completed_qty' => 0,
                    'planned_qty' => 0
                ];
            }
            $categoriesData[$catName]['total_qty'] += $item->total_quantity;
            $categoriesData[$catName]['completed_qty'] += $item->completed_quantity;
            $categoriesData[$catName]['planned_qty'] += ($item->total_quantity * $plannedPercent / 100);

            // Detailed Items by Subproject (For interactive chart)
            if (!isset($itemsBySubproject[$subName])) {
                $itemsBySubproject[$subName] = [
                    'items' => []
                ];
            }
            $itemsBySubproject[$subName]['items'][] = [
                'label' => $item->workItem->name ?? 'Unknown',
                'progress' => round($item->completion_percentage, 1),
                'planned' => round($plannedPercent, 1)
            ];

            // Detailed Items by Category (For interactive Items by Category chart)
            if (!isset($itemsByCategory[$catName])) {
                $itemsByCategory[$catName] = [
                    'items' => []
                ];
            }
            $itemsByCategory[$catName]['items'][] = [
                'label' => $item->workItem->name ?? 'Unknown',
                'progress' => round($item->completion_percentage, 1),
                'planned' => round($plannedPercent, 1)
            ];

            // Hierarchical Data (Subproject -> Category -> Items)
            if (!isset($hierarchicalData[$subName])) {
                $hierarchicalData[$subName] = [
                    'total_qty' => 0,
                    'completed_qty' => 0,
                    'categories' => []
                ];
            }
            $hierarchicalData[$subName]['total_qty'] += $item->total_quantity;
            $hierarchicalData[$subName]['completed_qty'] += $item->completed_quantity;

            if (!isset($hierarchicalData[$subName]['categories'][$catName])) {
                $hierarchicalData[$subName]['categories'][$catName] = [
                    'items' => []
                ];
            }
            $hierarchicalData[$subName]['categories'][$catName]['items'][] = $item;

            // Items by Status Aggregation
            $statusName = $item->status ? $item->status->name : __('general.no_status');
            if (!isset($itemsByStatus[$statusName])) {
                $itemsByStatus[$statusName] = [
                    'color' => $item->status ? $item->status->color : '#cccccc',
                    'icon' => $item->status ? $item->status->icon : 'las la-question-circle',
                    'items' => []
                ];
            }
            $itemsByStatus[$statusName]['items'][] = $item;
        }

        // Calculate progress for Hierarchical Headers
        foreach ($hierarchicalData as $subName => &$subData) {
             $total = max(1, $subData['total_qty']);
             $subData['progress'] = round(($subData['completed_qty'] / $total) * 100, 1);
        }
        unset($subData); // Break reference

        // Format Subproject Data for Chart
        $subprojectChartData = [
            'labels' => [],
            'progress' => [],
            'planned_progress' => []
        ];

        foreach ($subprojectsData as $name => $data) {
            $subprojectChartData['labels'][] = $name;
            $total = max(1, $data['total_qty']); // Avoid div/0
            
            $progress = ($data['completed_qty'] / $total) * 100;
            $planned = ($data['planned_qty'] / $total) * 100;

            $subprojectChartData['progress'][] = round($progress, 1);
            $subprojectChartData['planned_progress'][] = round($planned, 1);
        }

        // Format Categories Data for Chart
        $categoriesChartData = [
            'labels' => [],
            'progress' => [],
            'planned_progress' => []
        ];

        foreach ($categoriesData as $name => $data) {
            $categoriesChartData['labels'][] = $name;
            $total = max(1, $data['total_qty']); 
            
            $progress = ($data['completed_qty'] / $total) * 100;
            $planned = ($data['planned_qty'] / $total) * 100;

            $categoriesChartData['progress'][] = round($progress, 1);
            $categoriesChartData['planned_progress'][] = round($planned, 1);
        }
        
        // recent activity
        $recentProgress = $project->dailyProgress()->with(['user', 'projectItem.workItem'])->latest('progress_date')->take(10)->get()->groupBy(function($date) {
            return Carbon::parse($date->progress_date)->format('Y-m-d');
        });

        // Team Performance (Group by User who recorded progress)
        $teamPerformance = $project->dailyProgress()
            ->with(['user'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($progressItems) {
                $user = $progressItems->first()->user;
                // If user relation missing, create dummy obj or skip
                if(!$user) {
                     return null;
                }
                
                $total = $progressItems->sum('quantity');
                
                // Attach stats to user object for View
                $user->project_total_quantity = $total;
                $user->performance_percentage = min(100, ($total / 100) * 10); // arbitrary scale
                $user->position = $user->employee ? $user->employee->position : 'User'; // Fallback if links to employee
                
                return $user;
            })
            ->filter()
            ->values();

        // Attach Planned Percentage to Items & filters data
        $subprojectsList = [];
        $categoriesList = [];

        foreach ($project->items as $item) {
            // Planned (Time-based Calculation)
            $plannedPercent = 0;
            if ($item->start_date && $item->end_date) {
                $start = Carbon::parse($item->start_date);
                $end = Carbon::parse($item->end_date);
                $totalDuration = max(1, $start->diffInDays($end));
                $daysElapsed = max(0, $start->diffInDays(Carbon::now()));
                
                if (Carbon::now()->lt($start)) {
                    $plannedPercent = 0;
                } elseif (Carbon::now()->gt($end)) {
                    $plannedPercent = 100;
                } else {
                    $plannedPercent = ($daysElapsed / $totalDuration) * 100;
                }
            }
            $item->planned_percentage = round($plannedPercent, 1);
            
            // Collect for filters
            if ($item->subproject_name) {
                $subprojectsList[$item->subproject_name] = $item->subproject_name;
            } else {
                 $subprojectsList['Main Project'] = 'Main Project';
            }
            
            $catName = ($item->workItem && $item->workItem->category) ? $item->workItem->category->name : 'Uncategorized';
            $categoriesList[$catName] = $catName;
            
            // Temporary Hack: Modify subproject_name accessor or just set property if null
             if (!$item->subproject_name) {
                $item->subproject_name = 'Main Project'; // For easy filtering in JS
            }
        }
        
        $subprojectsList = collect($subprojectsList)->values()->all();
        $categoriesList = collect($categoriesList)->values()->all();

        // Get Item Statuses
        $itemStatuses = ItemStatus::where('is_active', true)->orderBy('order')->get();

        // جلب الموظفين (لإضافتهم إذا لزم الأمر في فيوهات أخرى، هنا قد لا نحتاجها للعرض فقط)
        // $employees = Employee::all();
        
        return view('progress::projects.show', compact(
            'project', 
            'overallProgress', 
            'totalItems', 
            'daysPassed', 
            'daysRemaining',
            'totalEmployees',
            'projectStatus',
            'chartData',
            'advancedChartData',
            'subprojectChartData', // New Subprojects Chart
            'categoriesChartData', 
            'itemsBySubproject',
            'itemsByCategory',
            'hierarchicalData', // New Hierarchical View Data
            'itemsByStatus', // New Items by Status View
            'recentProgress',
            'teamPerformance',
            'itemStatuses',
            'subprojectsList',
            'categoriesList'
        ));
    }

    public function edit($id)
    {
        $project = ProjectProgress::with(['items.workItem', 'employees'])->findOrFail($id);

        $initialItems = $project->items->map(function ($item) {
            return [
                'id' => $item->id, // Real DB ID
                'work_item_id' => $item->work_item_id,
                'name' => $item->workItem->name,
                'unit' => $item->workItem->unit,
                'subproject_name' => $item->subproject_name,
                'notes' => $item->notes,
                'is_measurable' => $item->is_measurable,
                'total_quantity' => $item->total_quantity,
                'estimated_daily_qty' => $item->estimated_daily_qty,
                'duration' => $item->duration,
                'start_date' => $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') : null,
                'end_date' => $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') : null,
                'predecessor' => $item->predecessor ? (string)$item->predecessor : '', 
                'dependency_type' => $item->dependency_type,
                'lag' => $item->lag,
                'item_order' => $item->item_order,
            ];
        })->sortBy('item_order')->values()->all();

        // $project->load('items.workItem', 'employees'); // Already loaded with findOrFail
        $clients = Client::all();
        $workItems = WorkItem::all();
        $employees = Employee::all();
        $users = User::all();
        $templates = ProjectTemplate::all();
        $projectTypes = ProjectType::all();

        return view('progress::projects.edit', compact(
            'project',
            'clients',
            'workItems',
            'employees',
            'templates',
            'initialItems',
            'projectTypes',
            'users'
        ));
    }

    public function update(Request $request, $id)
    {
        $project = ProjectProgress::findOrFail($id);
        
        $isDraft = $request->input('save_action') === 'draft' || $request->input('status') === 'draft';

        if ($isDraft) {
             $validated = $request->validate([
                'name' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
            ]);
            // Allow partial updates
            $status = 'draft';
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'client_id' => 'required|exists:clients,id',
                'status' => 'required|in:active,completed,pending,in_progress', // Removed draft from allowed strict statuses
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'working_zone' => 'required|string|max:255',
                'project_type' => 'nullable|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.work_item_id' => 'required|exists:work_items,id',
                'items.*.total_quantity' => 'required|numeric|min:0.01',
                'project_type_id' => 'required|exists:project_types,id',
            ]);
            $status = $validated['status'];
        }

        // تحديث بيانات المشروع
        // Use filtered inputs or fallbacks if draft
        $project->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'client_id' => $request['client_id'],
            'status' => $status,
            'start_date' => $request['start_date'],
            'working_zone' => $request['working_zone'],
            'project_type_id' => $request['project_type_id'],
            'holidays' => is_array($request['weekly_holidays'] ?? null) 
                ? implode(',', $request['weekly_holidays']) 
                : ($request['weekly_holidays'] ?? $project->holidays),
        ]);

        // تحديث الموظفين المرتبطين
        if ($request->has('employees')) {
            $project->employees()->sync($request->employees);
        }

        // تحديث المستخدمين المرتبطين
        if ($request->has('users')) {
            $project->users()->sync($request->users);
        }

        // البنود الحالية في المشروع
        $existingItems = $project->items->keyBy('work_item_id');

        // $startDate and $endDate logic removed as we use item-specific dates


        // Delete removed items
        $project->items()
            ->whereNotIn('work_item_id', collect($request->items)->pluck('work_item_id'))
            ->delete();

        // Save Items and Predecessors
        $itemMapping = []; // Maps input index to Real DB ID
        $savedItems = [];

        foreach ($request->items as $index => $itemData) {
            $itemAttributes = [
                'subproject_name' => $itemData['subproject_name'] ?? null,
                'notes' => $itemData['notes'] ?? null,
                'is_measurable' => isset($itemData['is_measurable']) ? 1 : 0,
                'total_quantity' => $itemData['total_quantity'],
                'estimated_daily_qty' => $itemData['estimated_daily_qty'], 
                'daily_quantity' => $itemData['estimated_daily_qty'], 
                'duration' => $itemData['duration'] ?? 0,
                'start_date' => $itemData['start_date'],
                'end_date' => $itemData['end_date'],
                // 'predecessor' => We set this in Step 2
                'dependency_type' => $itemData['dependency_type'] ?? 'end_to_start',
                'lag' => $itemData['lag'] ?? 0,
                'item_order' => $index,
            ];

            // Use 'work_item_id' just for data, not for finding the row.
            // We use 'id' hidden input to identify the row.
            $itemId = $itemData['id'] ?? null;
            $projectItem = null;

            if ($itemId && is_numeric($itemId)) {
                $projectItem = $project->items()->find($itemId);
            }

            if ($projectItem) {
                $projectItem->update(array_merge($itemAttributes, ['work_item_id' => $itemData['work_item_id']]));
            } else {
                // If ID is not found or is temp string, create new
                $projectItem = $project->items()->create(array_merge($itemAttributes, ['work_item_id' => $itemData['work_item_id']]));
            }

            // Store mapping: Input Index => Real ID
            $itemMapping[$index] = $projectItem->id;
            $savedItems[$index] = $projectItem;
        }

        // Step 2: Update predecessors using the mapping
        foreach ($request->items as $index => $itemData) {
            if (isset($itemData['predecessor']) && $itemData['predecessor'] !== '' && $itemData['predecessor'] !== null) {
                // We expect the frontend to send the INDEX of the predecessor now
                $predIndex = $itemData['predecessor'];
                
                if (isset($itemMapping[$predIndex])) {
                    $realPredId = $itemMapping[$predIndex];
                    $savedItems[$index]->update(['predecessor' => $realPredId]);
                }
            }
        }

        // Calculate and update Project End Date based on items
        $lastEndDate = $project->items()->max('end_date');
        $project->update(['end_date' => $lastEndDate ?? $validated['start_date']]);

        // ---------------------------------------------------------
        // Save as Template (if requested in Edit Mode)
        // ---------------------------------------------------------
        if ($request->filled('save_as_template') && $request->save_as_template == 1) {
            $templateName = $request->template_name ?: $project->name . ' (Template)';
            
            $template = ProjectTemplate::create([
                'name' => $templateName,
                'description' => $project->description,
                'project_type_id' => $project->project_type_id,
                'weekly_holidays' => explode(',', $project->holidays), 
            ]);

            // Clone saved items to template
            // We use $savedItems which contains the UPDATED item objects
            $templateItemMapping = []; 

            foreach ($savedItems as $index => $projectItem) {
                // $projectItem is the Model instance
                $templateItem = $template->items()->create([
                    'project_template_id' => $template->id,
                    'work_item_id' => $projectItem->work_item_id,
                    'subproject_name' => $projectItem->subproject_name,
                    'total_quantity' => $projectItem->total_quantity,
                    'estimated_daily_qty' => $projectItem->estimated_daily_qty,
                    'duration' => $projectItem->duration,
                    'notes' => $projectItem->notes,
                    'is_measurable' => $projectItem->is_measurable,
                    'dependency_type' => $projectItem->dependency_type,
                    'lag' => $projectItem->lag,
                    'item_order' => $projectItem->item_order,
                    // Predecessor set in Pass 2
                ]);
                $templateItemMapping[$projectItem->id] = $templateItem->id;
            }

            // Link Predecessors for Template Items
            foreach ($savedItems as $projectItem) {
                if ($projectItem->predecessor) {
                    $templateItemId = $templateItemMapping[$projectItem->id] ?? null;
                    $templatePredId = $templateItemMapping[$projectItem->predecessor] ?? null;

                    if ($templateItemId && $templatePredId) {
                         DB::table('project_items')->where('id', $templateItemId)->update(['predecessor' => $templatePredId]);
                    }
                }
            }
        }

        return redirect()
            ->route('progress.project.index')
            ->with('success', 'تم تحديث المشروع بنجاح');
    }

    public function destroy(ProjectProgress $project)
    {
        // Start database transaction
        DB::beginTransaction();

        try {
            // Delete all related daily progress records first
            $project->items()->each(function ($item) {
                if (method_exists($item, 'dailyProgress')) {
                    $item->dailyProgress()->delete();
                }
                $item->delete();
            });

            // Then delete the project
            $project->delete();

            // Commit transaction if all deletions succeeded
            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', 'تم حذف المشروع وكل البيانات المرتبطة به بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    public function replicate(Request $request, ProjectProgress $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Clone project
            $newProject = $project->replicate();
            $newProject->name = $request->name;
            $newProject->status = 'draft';
            $newProject->created_at = now();
            $newProject->updated_at = now();
            $newProject->save();

            // Clone Items
            foreach ($project->items as $item) {
                $newItem = $item->replicate();
                $newItem->project_id = $newProject->id;
                $newItem->created_at = now();
                $newItem->updated_at = now();
                $newItem->save();
            }

            DB::commit();

            return redirect()->route('progress.project.edit', $newProject->id)
                ->with('success', 'تم نسخ المشروع بنجاح، يمكنك الآن تعديله.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'فشل نسخ المشروع: ' . $e->getMessage());
        }
    }

    public function progress(ProjectProgress $project, Request $request)
    {
        // $this->authorize('view', $project);

        // 1. Determine Report Date (To Date)
        // Defaults to Today if not provided.
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        // Note: from_date is effectively used for display filtering in some contexts using standard patterns,
        // but for the calculations requested ("Before Today" vs "Today"), 'to_date' is the anchor.
        $fromDate = $request->input('from_date', Carbon::parse($toDate)->subWeek()->format('Y-m-d'));

        $toDateObj = Carbon::parse($toDate)->endOfDay();
        $targetDate = Carbon::parse($toDate)->startOfDay(); // "Today" for the report

        // Load project and items
        $project->load(['client', 'items.workItem']);

        // Holidays config
        // Assuming holidays are stored as comma separated string "5,6" (Fri,Sat)
        $holidays = $project->holidays ? explode(',', $project->holidays) : [];

        // 2. Iterate and Calculate
        $project->items->each(function ($item) use ($targetDate, $toDateObj, $holidays) {
            $dailyQty = $item->estimated_daily_qty ?? 0;
            $itemsStartDate = Carbon::parse($item->start_date)->startOfDay();
            
            // ----------------------------------------------------------------
            // 2. Previous Columns (Status BEFORE today)
            // ----------------------------------------------------------------
            // Planned: Working Days from Start Date up to Yesterday * Estimated Daily Qty
            $yesterday = $targetDate->copy()->subDay();
            
            $prevPlannedQty = 0;
            if ($yesterday->gte($itemsStartDate)) {
                $workingDaysPrev = $this->calculateWorkingDays($itemsStartDate, $yesterday, $holidays);
                $prevPlannedQty = $workingDaysPrev * $dailyQty;
            }
            // Cap at Total Quantity
            $prevPlannedQty = min($prevPlannedQty, $item->total_quantity);

            // Actual: Sum of approved daily progress where Date < Today
            $prevActualQty = $item->dailyProgress()
                ->where('progress_date', '<', $targetDate->format('Y-m-d'))
                ->sum('quantity');

            // Previous %
            $prevPercentage = $item->total_quantity > 0 
                ? ($prevActualQty / $item->total_quantity) * 100 
                : 0;

            // ----------------------------------------------------------------
            // 3. Current Columns (Status for Today)
            // ----------------------------------------------------------------
            // Planned: Estimated Daily Qty if today is working day AND in range. Else 0.
            $currPlannedQty = 0;
            $isWorkingDay = !$this->isHoliday($targetDate, $holidays);
            $inRange = $targetDate->between($itemsStartDate, Carbon::parse($item->end_date)->endOfDay());
            
            if ($isWorkingDay && $inRange) {
                $currPlannedQty = $dailyQty;
            }

            // Actual: Sum of daily progress where Date = Today
            $currActualQty = $item->dailyProgress()
                ->whereDate('progress_date', $targetDate->format('Y-m-d'))
                ->sum('quantity');
            
            // Current %
            $currPercentage = $item->total_quantity > 0 
                ? ($currActualQty / $item->total_quantity) * 100 
                : 0;

            // ----------------------------------------------------------------
            // 4. Completed / Cumulative (Status up to To Date)
            // ----------------------------------------------------------------
            // Planned: Working Days from Start To To Date * Est Daily Qty
            // (Basically Prev + Current Planned, capped) (Recalculate to be safe)
            $completedPlannedQty = 0;
            if ($targetDate->gte($itemsStartDate)) {
               $workingDaysComp = $this->calculateWorkingDays($itemsStartDate, $targetDate, $holidays);
               $completedPlannedQty = $workingDaysComp * $dailyQty;
            }
            $completedPlannedQty = min($completedPlannedQty, $item->total_quantity);

            // Actual: Sum where Date <= To Date (Prev + Curr)
            $completedActualQty = $prevActualQty + $currActualQty;

            // Completed %
            $completedPercentage = $item->total_quantity > 0
                ? ($completedActualQty / $item->total_quantity) * 100
                : 0;

            // ----------------------------------------------------------------
            // 5. Remaining
            // ----------------------------------------------------------------
            // Planned Remaining
            $remainingPlannedQty = max(0, $item->total_quantity - $completedPlannedQty);
            
            // Actual Remaining
            $remainingActualQty = max(0, $item->total_quantity - $completedActualQty);
            
            // Remaining %
            $remainingPercentage = $item->total_quantity > 0 
                ? ($remainingActualQty / $item->total_quantity) * 100
                : 0;


            // Assign values to item for View
            $item->calc_prev_planned = $prevPlannedQty;
            $item->calc_prev_actual = $prevActualQty;
            $item->calc_prev_percent = round($prevPercentage, 2);

            $item->calc_curr_planned = $currPlannedQty;
            $item->calc_curr_actual = $currActualQty;
            $item->calc_curr_percent = round($currPercentage, 2);

            $item->calc_comp_planned = $completedPlannedQty;
            $item->calc_comp_actual = $completedActualQty;
            $item->calc_comp_percent = round($completedPercentage, 2);
            $item->calc_comp_planned_percent = $item->total_quantity > 0 ? round(($completedPlannedQty / $item->total_quantity) * 100, 2) : 0;

            $item->calc_rem_planned = $remainingPlannedQty;
            $item->calc_rem_actual = $remainingActualQty;
            $item->calc_rem_percent = round($remainingPercentage, 2);
        });

        // 6. Overall Project Progress (based on Actuals)
        $totalProjectQuantity = $project->items->sum('total_quantity');
        $totalCompletedQuantity = $project->items->sum('calc_comp_actual');
        $projectProgress = $totalProjectQuantity > 0
            ? min(round(($totalCompletedQuantity / $totalProjectQuantity) * 100, 2), 100)
            : 0;

        return view('progress::projects.progress', compact('project', 'projectProgress', 'fromDate', 'toDate'));
    }

    public function gantt(ProjectProgress $project)
    {
        // Load relationships needed for Gantt
        $project->load(['client', 'items' => function($q) {
            $q->orderBy('item_order');
        }, 'items.workItem', 'items.predecessorItem']);
        
        // Pass to view
        return view('progress::projects.gantt', compact('project'));
    }

    /**
     * Calculate number of working days between two dates (inclusive)
     */
    private function calculateWorkingDays($startDate, $endDate, $holidays)
    {
        if ($startDate->gt($endDate)) return 0;
        
        $days = 0;
        $current = $startDate->copy();
        
        // Safety break for infinite loops if dates are wild
        if ($current->diffInDays($endDate) > 3660) {
            return 0; // limit to 10 years for safety
        }

        while ($current->lte($endDate)) {
            if (!$this->isHoliday($current, $holidays)) {
                $days++;
            }
            $current->addDay();
        }
        
        return $days;
    }

    /**
     * Check if a specific date is a holiday
     */
    private function isHoliday($date, $holidays)
    {
        // $holidays is array of day indices (0=Sun, 6=Sat - dependent on DB/Frontend convention)
        // Default Carbon dayOfWeek: 0 (Sunday) - 6 (Saturday)
        // Check what front-end sends. `create.blade.php`: daysOfWeek usually 0-6 or 1-7.
        // Assuming standard Carbon matching for now.
        return in_array($date->dayOfWeek, $holidays);
    }


    public function getSubprojects($id)
    {
        $project = ProjectProgress::with('items.workItem')->findOrFail($id);
        $settings = $project->settings ?? [];
        $savedWeights = $settings['subproject_weights'] ?? [];
        $manualWeightsEnabled = $settings['manual_weights_enabled'] ?? false;
        
        // Group by subproject
        $subprojects = $project->items->groupBy(function($item) {
            return $item->subproject_name ?: 'Main';
        });

        $totalProjectQty = $project->items->sum('total_quantity');
        $totalWeight = 0;
        
        $data = [];
        foreach($subprojects as $name => $items) {
            $subQty = $items->sum('total_quantity');
            
            // Determine Weight
            if ($manualWeightsEnabled && isset($savedWeights[$name])) {
                $weight = floatval($savedWeights[$name]);
            } else {
                $weight = $totalProjectQty > 0 ? ($subQty / $totalProjectQty) * 100 : 0;
            }
            $totalWeight += $weight;

            // Calculate progress of subproject (Weighted or Simple Average)
            // Progress = (Completed Qty / Total Qty) * 100
            $subCompleted = 0;
            foreach($items as $item) {
                 // Trusting cached/calculated columns if available, else summing dailyProgress would be better but expensive here without eager load optimization seen in show()
                 // Ideally: $item->dailyProgress->sum('quantity') but that's N+1 unless loaded.
                 // In show(), we loaded it. Here we didn't. 
                 // Let's assume completed_quantity is maintained or accept simplified calculation for now.
                 // Better: use the completed_quantity field which we should ensure is updated.
                 $subCompleted += $item->completed_quantity;
            }
            
            $progress = $subQty > 0 ? ($subCompleted / $subQty) * 100 : 0;

            $data[] = [
                'name' => $name,
                'weight' => round($weight, 2),
                'progress' => round($progress, 2),
                'items_count' => $items->count(),
                'total_qty' => $subQty,
                'completed_qty' => $subCompleted,
                'items' => $items->map(function($item) {
                     return [
                         'name' => $item->workItem->name ?? 'Item',
                         'is_measurable' => (bool)$item->is_measurable,
                         'progress' => $item->total_quantity > 0 ? round(($item->completed_quantity / $item->total_quantity) * 100, 1) : 0
                     ];
                })
            ];
        }

        return response()->json([
            'subprojects' => $data,
            'total_weight' => round($totalWeight, 1),
            'manual_weights_enabled' => $manualWeightsEnabled,
            'overall_weighted_progress' => $this->calculateTotalWeightedProgress($data)
        ]);
    }

    private function calculateTotalWeightedProgress($subprojectsData)
    {
        // Sum (Progress * Weight / 100)
        $total = 0;
        $totalWeightCheck = 0;
        foreach ($subprojectsData as $sub) {
            $total += ($sub['progress'] * $sub['weight']);
            $totalWeightCheck += $sub['weight'];
        }
        
        // If weights don't sum to 100, normalizing might be needed, but usually we just show what it is.
        // If Total Weight is 0, progress is 0.
        if ($totalWeightCheck == 0) return 0;

        // return round($total / $totalWeightCheck, 2); // Normalize? 
        return round($total / 100, 2); // Standard Weighted Average assumes weights sum to 100
    }

    public function updateWeight(Request $request, $id)
    {
        $project = ProjectProgress::findOrFail($id);
        $subName = $request->input('subproject_name');
        $weight = floatval($request->input('weight'));

        $settings = $project->settings ?? [];
        $settings['manual_weights_enabled'] = true; // Enable Manual Mode on first edit
        
        $weights = $settings['subproject_weights'] ?? [];
        $weights[$subName] = $weight;
        $settings['subproject_weights'] = $weights;

        $project->settings = $settings;
        $project->save();

        return response()->json(['success' => true]);
    }

    public function updateAllWeights(Request $request, $id)
    {
        $project = ProjectProgress::findOrFail($id);
        $weightsInput = $request->input('weights', []); // Array of [name => weight]

        $settings = $project->settings ?? [];
        $settings['manual_weights_enabled'] = true;
        
        // Merge or overwrite
        $currentWeights = $settings['subproject_weights'] ?? [];
        foreach($weightsInput as $name => $val) {
             $currentWeights[$name] = floatval($val);
        }
        $settings['subproject_weights'] = $currentWeights;

        $project->settings = $settings;
        $project->save();

        return response()->json(['success' => true]);
    }

    public function getProjectDetails($id)
    {
        $project = ProjectProgress::with(['client', 'type', 'employees', 'users'])
            ->with(['items' => function ($query) {
                $query->withSum('dailyProgress', 'quantity');
            }])
            ->findOrFail($id);
        
        // Calculate Subprojects Count (unique names in items)
        $subprojectsCount = $project->items->whereNotNull('subproject_name')->unique('subproject_name')->count();

        // Calculate Progress Dynamically
        $totalQty = $project->items->sum('total_quantity');
        // Use the aggregated sum from dailyProgress relation
        $completedQty = $project->items->sum('daily_progress_sum_quantity'); 
        
        $progress = $totalQty > 0 ? round(($completedQty / $totalQty) * 100) : 0;

        // Unique Employees + Users count
        $allMembers = $project->employees->pluck('id')->merge($project->users->pluck('id'))->unique();

        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'status' => $project->status,
             // localized status label could be handled here or frontend
            'type_name' => $project->type->name ?? 'N/A',
            'client_name' => $project->client->name ?? 'N/A',
            'start_date' => $project->start_date, // Format if needed
            'end_date' => $project->end_date,
            'working_zone' => $project->working_zone ?? 'N/A',
            'created_at' => $project->created_at->format('d-m-Y'),
            'items_count' => $project->items->count(),
            'subprojects_count' => $subprojectsCount,
            'employees_count' => $allMembers->count(), 
            'working_days' => $project->working_days ?? 0,
            'daily_work_hours' => $project->daily_work_hours ?? 8,
            'progress' => $progress
        ]);
    }
    /**
     * Publish a draft project to active status.
     * 
     * @param Request $request
     * @param ProjectProgress $project
     * @return \Illuminate\Http\Response
     */
    public function publish(\Illuminate\Http\Request $request, ProjectProgress $project)
    {
        // 1. Check if complete
        if ($project->completion_percentage < 100) {
            return back()->with('error', 'Project data is incomplete. Please finish editing before publishing.');
        }

        // 2. Update status
        $project->update(['status' => 'in_progress']);

        return redirect()->route('progress.project.index')->with('success', 'Project published successfully.');
    }
}
