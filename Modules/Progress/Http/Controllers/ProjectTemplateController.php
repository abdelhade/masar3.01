<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\TemplateItem;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\Subproject; 
use Modules\Progress\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\WorkItemCategory;
use Modules\Progress\Models\ProjectType;
use Illuminate\Support\Facades\Log;

class ProjectTemplateController extends Controller
{
    protected TemplateService $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
        
        $this->middleware('can:view progress-project-templates')->only('index');
        $this->middleware('can:create progress-project-templates')->only(['create', 'store']);
        $this->middleware('can:edit progress-project-templates')->only(['edit', 'update']);
        $this->middleware('can:delete progress-project-templates')->only('destroy');
        $this->middleware('can:view progress-project-templates')->only(['show', 'debugPredecessors']);
    }

    public function index()
    {
        $templates = ProjectTemplate::withCount('items')
            ->orderBy('id','desc')
            ->paginate(15);

        return view('progress::project_templates.index', compact('templates'));
    }

    public function create()
    {
        $categories = WorkItemCategory::with(['workItems' => function($query) {
            $query->orderBy('order');
        }])->get();

        $projectTypes = ProjectType::all();
        $workItems = WorkItem::with('category')->orderBy('order')->get();

        return view('progress::project_templates.create', compact('categories', 'projectTypes', 'workItems'));
    }

    public function store(Request $request)
    {
        // Debug: Log received data
        Log::info('=== Template Store Request ===');
        Log::info('Request data:', $request->all());
        Log::info('Items count:', ['count' => count($request->input('items', []))]);
        Log::info('Items data:', $request->input('items', []));
        
        try {
            $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'status' => ['nullable','string'],
            'project_type_id' => ['nullable','exists:project_types,id'],
            'items' => ['required','array','min:1'],
            'items.*.work_item_id' => ['required','exists:work_items,id'],
            'items.*.default_quantity' => ['required','numeric','min:0'],
            'items.*.estimated_daily_qty' => ['nullable','numeric','min:0'],
            'items.*.duration' => ['nullable','integer','min:0'],
            'items.*.predecessor' => ['nullable','string'],
            'items.*.dependency_type' => ['nullable','string'],
            'items.*.lag' => ['nullable','integer'],
            'items.*.notes' => ['nullable','string'],
            'items.*.item_order' => ['nullable','integer'], // Optional - we use $order++ like ProjectService
            'items.*.subproject_name' => ['nullable','string','max:255'], // ✅ مثل Projects
            'items.*.start_date' => ['nullable','date'],
            'items.*.end_date' => ['nullable','date'],
            'subprojects' => ['nullable','array'], // ✅ مثل Projects
            'subprojects.*.name' => ['nullable','string','max:255'],
            'subprojects.*.start_date' => ['nullable','date'],
            'subprojects.*.end_date' => ['nullable','date'],
            'subprojects.*.total_quantity' => ['nullable','numeric'],
            'subprojects.*.unit' => ['nullable','string','max:50'],
        ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Template store error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'error_details' => config('app.debug') ? $e->getTraceAsString() : null
                ], 500);
            }
            throw $e;
        }

        DB::transaction(function () use ($validated) {
            Log::info('=== Creating Template ===');
            Log::info('Validated items count:', ['count' => count($validated['items'] ?? [])]);
            Log::info('Validated items data:', $validated['items'] ?? []);
            
            $template = ProjectTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'project_type_id' => $validated['project_type_id'] ?? null,
            ]);

            Log::info('Template created:', ['id' => $template->id, 'name' => $template->name]);

            // Use TemplateService to create items (same logic as ProjectService)
            if (!empty($validated['items'])) {
                $itemMapping = $this->templateService->createTemplateItems($template, $validated['items'], $validated);
                $this->templateService->updateItemPredecessors($validated['items'], $itemMapping);
            }

            // حفظ المشاريع الفرعية
            if (!empty($validated['subprojects'])) {
                $this->templateService->createSubprojects($template, $validated['subprojects']);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء القالب بنجاح.'
            ]);
        }
        
        return redirect()
            ->route('project-templates.index')
            ->with('success', 'تم إنشاء القالب بنجاح.');
    }

public function show(ProjectTemplate $project_template)
{
    $project_template->load([
        'items' => function($query) {
            $query->orderBy('item_order', 'asc');
        },
        'items.workItem.category',
        'projectType',
        'items.predecessorItem.workItem'
    ]);

    return view('progress::project_templates.show', compact('project_template'));
}

    public function edit(ProjectTemplate $project_template)
    {
        $project_template->load(['items.workItem.category', 'projectType']);

        $categories = WorkItemCategory::with(['workItems' => function($query) {
            $query->orderBy('order');
        }])->get();

        $projectTypes = ProjectType::all();
        $workItems = WorkItem::with('category')->orderBy('order')->get();

        return view('progress::project_templates.edit', compact('project_template', 'categories', 'projectTypes', 'workItems'));
    }

    public function update(Request $request, ProjectTemplate $project_template)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'status' => ['nullable','string'],
            'project_type_id' => ['nullable','exists:project_types,id'],
            'items' => ['required','array','min:1'],
            'items.*.id' => ['nullable','integer','exists:template_items,id'],
            'items.*.work_item_id' => ['required','exists:work_items,id'],
            'items.*.default_quantity' => ['required','numeric','min:0'],
            'items.*.estimated_daily_qty' => ['nullable','numeric','min:0'],
            'items.*.duration' => ['nullable','integer','min:0'],
            'items.*.predecessor' => ['nullable','string'],
            'items.*.dependency_type' => ['nullable','string'],
            'items.*.lag' => ['nullable','integer'],
            'items.*.notes' => ['nullable','string'],
            'items.*.item_order' => ['nullable','integer'],
            'items.*.subproject_name' => ['nullable','string','max:255'], // ✅ مثل Projects
            'subprojects' => ['nullable','array'], // ✅ مثل Projects
            'subprojects.*.name' => ['nullable','string','max:255'],
            'subprojects.*.start_date' => ['nullable','date'],
            'subprojects.*.end_date' => ['nullable','date'],
            'subprojects.*.total_quantity' => ['nullable','numeric'],
            'subprojects.*.unit' => ['nullable','string','max:50'],
        ]);

        DB::transaction(function () use ($validated, $project_template) {
            // تحديث بيانات القالب الأساسية
            $project_template->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'project_type_id' => $validated['project_type_id'] ?? null,
                'working_days' => $validated['working_days'] ?? 5,
                'daily_work_hours' => $validated['daily_work_hours'] ?? 8,
                'weekly_holidays' => $validated['weekly_holidays'] ?? '5,6',
            ]);

            // Use TemplateService to sync items (same logic as ProjectService)
            if (!empty($validated['items'])) {
                $itemMapping = $this->templateService->syncTemplateItems($project_template, $validated['items'], $validated);
                $this->templateService->updateItemPredecessors($validated['items'], $itemMapping);
            } else {
                // إذا لم يتم إرسال بنود، احذف كل البنود القديمة
                $project_template->items()->delete();
            }

            // تحديث المشاريع الفرعية
            if (!empty($validated['subprojects'])) {
                $this->templateService->updateSubprojects($project_template, $validated['subprojects']);
            }
        });

        return redirect()
            ->route('project-templates.index')
            ->with('success', 'تم تحديث القالب بنجاح.');
    }

    public function destroy(ProjectTemplate $project_template)
    {
        $project_template->delete();

        return redirect()
            ->route('project-templates.index')
            ->with('success', 'تم حذف القالب بنجاح.');
    }
    
    /**
     * Debug and fix predecessor issues in templates
     */
    public function debugPredecessors(ProjectTemplate $project_template)
    {
        $items = $project_template->items()->with('workItem')->get();
        $debug = [];
        
        foreach ($items as $item) {
            $predecessorInfo = null;
            
            if ($item->predecessor) {
                // البحث عن الـ predecessor في نفس الـ template
                $predecessorItem = $items->where('work_item_id', $item->predecessor)->first();
                
                if ($predecessorItem) {
                    $predecessorInfo = [
                        'found_in_template' => true,
                        'predecessor_name' => $predecessorItem->workItem->name ?? 'Unknown',
                        'predecessor_work_item_id' => $predecessorItem->work_item_id
                    ];
                } else {
                    // البحث في WorkItems مباشرة
                    $workItem = \Modules\Progress\Models\WorkItem::find($item->predecessor);
                    $predecessorInfo = [
                        'found_in_template' => false,
                        'predecessor_name' => $workItem->name ?? 'Not Found',
                        'predecessor_work_item_id' => $item->predecessor,
                        'exists_in_work_items' => $workItem ? true : false
                    ];
                }
            }
            
            $debug[] = [
                'template_item_id' => $item->id,
                'work_item_id' => $item->work_item_id,
                'work_item_name' => $item->workItem->name ?? 'Unknown',
                'predecessor' => $item->predecessor,
                'predecessor_info' => $predecessorInfo
            ];
        }
        
        return response()->json([
            'template_id' => $project_template->id,
            'template_name' => $project_template->name,
            'total_items' => $items->count(),
            'items_with_predecessors' => $items->where('predecessor', '!=', null)->count(),
            'debug_info' => $debug
        ]);
    }

    public function items(ProjectTemplate $project_template)
    {
        $items = $project_template->items()
            ->with('workItem')
            ->orderBy('item_order')
            ->get()
            ->map(function($item) {
                // Get predecessor work_item_id if predecessor exists
                $predecessorWorkItemId = null;
                if ($item->predecessor) {
                    $predecessorItem = ProjectItem::find($item->predecessor);
                    if ($predecessorItem) {
                        $predecessorWorkItemId = $predecessorItem->work_item_id;
                    }
                }
                
                return [
                    'id' => $item->id,
                    'work_item_id' => $item->work_item_id,
                    'work_item_name' => $item->workItem->name,
                    'default_quantity' => $item->total_quantity, // Use total_quantity from project_items
                    'estimated_daily_qty' => $item->estimated_daily_qty,
                    'duration' => $item->duration,
                    'predecessor' => $predecessorWorkItemId, // Return work_item_id for compatibility
                    'dependency_type' => $item->dependency_type,
                    'lag' => $item->lag,
                    'notes' => $item->notes,
                    'item_order' => $item->item_order,
                ];
            });

        return response()->json($items);
    }

    public function getTemplateData(ProjectTemplate $project_template)
    {
        $templateItems = $project_template->items()
            ->with('workItem.category')
            ->orderBy('item_order')
            ->get();

        // Format items for JavaScript consumption
        $items = $templateItems->map(function($item) use ($templateItems) {
            $predecessor = $item->predecessor;
            $predecessorName = null;
            $predecessorWorkItemId = null;
            
            // البحث عن اسم الـ predecessor (predecessor is project_item_id in project_items)
            if ($predecessor) {
                $predecessorItem = $templateItems->find($predecessor);
                if ($predecessorItem && $predecessorItem->workItem) {
                    $predecessorName = $predecessorItem->workItem->name;
                    $predecessorWorkItemId = $predecessorItem->work_item_id;
                } else {
                    // Fallback: search by project_item_id
                    $predecessorProjectItem = ProjectItem::find($predecessor);
                    if ($predecessorProjectItem && $predecessorProjectItem->workItem) {
                        $predecessorName = $predecessorProjectItem->workItem->name;
                        $predecessorWorkItemId = $predecessorProjectItem->work_item_id;
                    }
                }
            }
            
            return [
                'work_item_id' => $item->work_item_id,
                'work_item' => $item->workItem ? [
                    'id' => $item->workItem->id,
                    'name' => $item->workItem->name,
                    'unit' => $item->workItem->unit,
                    'expected_quantity_per_day' => $item->workItem->expected_quantity_per_day,
                    'duration' => $item->workItem->duration,
                    'category' => $item->workItem->category ? [
                        'id' => $item->workItem->category->id,
                        'name' => $item->workItem->category->name,
                    ] : null,
                ] : null,
                'total_quantity' => $item->total_quantity,
                'default_quantity' => $item->total_quantity, // Use total_quantity from project_items
                'estimated_daily_qty' => $item->estimated_daily_qty,
                'duration' => $item->duration,
                'predecessor' => $predecessorWorkItemId, // Return work_item_id for compatibility
                'predecessor_name' => $predecessorName,
                'dependency_type' => $item->dependency_type,
                'lag' => $item->lag,
                'notes' => $item->notes,
                'subproject_name' => $item->subproject_name,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'planned_end_date' => $item->planned_end_date,
                'is_measurable' => $item->is_measurable ?? false,
                'item_label' => $item->item_label,
                'daily_quantity' => $item->daily_quantity,
                'shift' => $item->shift,
                'item_order' => $item->item_order,
            ];
        });

        // Also provide grouped by category for backward compatibility
        $templateData = $templateItems
            ->groupBy('workItem.category.name')
            ->map(function($items, $categoryName) use ($templateItems) {
                return [
                    'category_name' => $categoryName,
                    'items' => $items->map(function($item) use ($templateItems) {
                        $predecessor = $item->predecessor;
                        $predecessorName = null;
                        $predecessorWorkItemId = null;
                        
                        // predecessor is project_item_id in project_items
                        if ($predecessor) {
                            $predecessorItem = $templateItems->find($predecessor);
                            if ($predecessorItem && $predecessorItem->workItem) {
                                $predecessorName = $predecessorItem->workItem->name;
                                $predecessorWorkItemId = $predecessorItem->work_item_id;
                            } else {
                                $predecessorProjectItem = ProjectItem::find($predecessor);
                                if ($predecessorProjectItem && $predecessorProjectItem->workItem) {
                                    $predecessorName = $predecessorProjectItem->workItem->name;
                                    $predecessorWorkItemId = $predecessorProjectItem->work_item_id;
                                }
                            }
                        }
                        
                        return [
                            'work_item_id' => $item->work_item_id,
                            'name' => $item->workItem->name,
                            'total_quantity' => $item->total_quantity,
                            'default_quantity' => $item->total_quantity, // Use total_quantity from project_items
                            'estimated_daily_qty' => $item->estimated_daily_qty,
                            'duration' => $item->duration,
                            'predecessor' => $predecessorWorkItemId, // Return work_item_id for compatibility
                            'predecessor_name' => $predecessorName,
                            'dependency_type' => $item->dependency_type,
                            'lag' => $item->lag,
                            'notes' => $item->notes,
                            'subproject_name' => $item->subproject_name,
                            'start_date' => $item->start_date,
                            'end_date' => $item->end_date,
                            'planned_end_date' => $item->planned_end_date,
                            'is_measurable' => $item->is_measurable ?? false,
                            'item_label' => $item->item_label,
                            'daily_quantity' => $item->daily_quantity,
                            'shift' => $item->shift,
                            'item_order' => $item->item_order,
                        ];
                    })
                ];
            })->values();

        return response()->json([
            'template_name' => $project_template->name,
            'status' => $project_template->status,
            'project_type_id' => $project_template->project_type_id,
            'working_days' => $project_template->working_days,
            'daily_work_hours' => $project_template->daily_work_hours,
            'weekly_holidays' => $project_template->weekly_holidays,
            'working_zone' => $project_template->working_zone,
            'items' => $items,  // ✅ Add this for JavaScript compatibility
            'categories' => $templateData
        ]);
    }

    /**
     * ✅ حفظ القالب من بيانات النموذج مباشرة (قبل إنشاء المشروع)
     * بدون validations - يحفظ مباشرة
     */
    public function storeFromForm(Request $request)
    {
        try {
            // ✅ بدون validations - نأخذ البيانات مباشرة من الـ request
            $name = $request->input('name', 'Template ' . date('Y-m-d H:i:s'));
            $description = $request->input('description');
            $projectTypeId = $request->input('project_type_id');
            $items = $request->input('items', []);
            $subprojects = $request->input('subprojects', []);
            $workingDays = $request->input('working_days');
            $dailyWorkHours = $request->input('daily_work_hours');
            $weeklyHolidays = $request->input('weekly_holidays');
            
            // Convert empty string to null (user doesn't want to save weekly holidays)
            $weeklyHolidays = !empty($weeklyHolidays) ? $weeklyHolidays : null;

            DB::transaction(function () use ($name, $description, $projectTypeId, $items, $subprojects, $workingDays, $dailyWorkHours, $weeklyHolidays) {
                $template = ProjectTemplate::create([
                    'name' => $name,
                    'description' => $description ?? null,
                    'status' => 'active',
                    'project_type_id' => $projectTypeId ?? null,
                    'working_days' => $workingDays ?? null,
                    'daily_work_hours' => $dailyWorkHours ?? null,
                    'weekly_holidays' => $weeklyHolidays,
                ]);

                // Create mapping of work_item_id to project_item_id
                $workItemToProjectItemMap = [];
                // Create mapping of rowId (from form) to work_item_id
                $rowIdToWorkItemIdMap = [];
                
                // First pass: Create items without predecessors in project_items table
                $itemOrder = 0;
                foreach ($items as $rowId => $item) {
                    // Skip if work_item_id is missing
                    if (empty($item['work_item_id'])) {
                        continue;
                    }
                    
                    // Map rowId to work_item_id for predecessor lookup
                    $rowIdToWorkItemIdMap[$rowId] = $item['work_item_id'];
                    
                    $totalQuantity = isset($item['default_quantity']) && $item['default_quantity'] !== '' 
                        ? (float)$item['default_quantity'] 
                        : (isset($item['total_quantity']) && $item['total_quantity'] !== '' 
                            ? (float)$item['total_quantity'] 
                            : 1);
                    
                    $projectItem = ProjectItem::create([
                        'project_id' => null, // Template items don't have project_id
                        'project_template_id' => $template->id,
                        'work_item_id' => $item['work_item_id'],
                        'total_quantity' => $totalQuantity,
                        'completed_quantity' => 0, // Template items have no progress
                        'remaining_quantity' => $totalQuantity,
                        'estimated_daily_qty' => isset($item['estimated_daily_qty']) && $item['estimated_daily_qty'] !== '' 
                            ? (float)$item['estimated_daily_qty'] 
                            : 0,
                        'duration' => isset($item['duration']) && $item['duration'] !== '' 
                            ? (int)$item['duration'] 
                            : 0,
                        'item_label' => $item['item_label'] ?? null,
                        'daily_quantity' => $item['daily_quantity'] ?? null,
                        'shift' => $item['shift'] ?? null,
                        'predecessor' => null, // Will be set in second pass
                        'dependency_type' => $item['dependency_type'] ?? 'end_to_start',
                        'lag' => isset($item['lag']) && $item['lag'] !== '' ? (int)$item['lag'] : 0,
                        'notes' => $item['notes'] ?? null,
                        'item_order' => isset($item['item_order']) && $item['item_order'] !== '' 
                            ? (int)$item['item_order'] 
                            : $itemOrder++,
                        'subproject_name' => $item['subproject_name'] ?? null,
                        'start_date' => $item['start_date'] ?? now()->format('Y-m-d'),
                        'end_date' => $item['end_date'] ?? now()->addDays(30)->format('Y-m-d'),
                        'planned_end_date' => $item['planned_end_date'] ?? ($item['end_date'] ?? now()->addDays(30)->format('Y-m-d')),
                        'is_measurable' => isset($item['is_measurable']) ? (bool)$item['is_measurable'] : false,
                    ]);
                    $workItemToProjectItemMap[$item['work_item_id']] = $projectItem->id;
                }
                
                // Second pass: Update predecessors
                // Predecessor comes as rowId from form, need to convert to work_item_id first
                foreach ($items as $rowId => $item) {
                    if (!empty($item['predecessor']) && !empty($item['work_item_id']) && isset($workItemToProjectItemMap[$item['work_item_id']])) {
                        $currentProjectItemId = $workItemToProjectItemMap[$item['work_item_id']];
                        
                        // Predecessor is rowId (like "item_1_1764877607624"), convert to work_item_id
                        $predecessorRowId = $item['predecessor'];
                        if (isset($rowIdToWorkItemIdMap[$predecessorRowId])) {
                            $predecessorWorkItemId = $rowIdToWorkItemIdMap[$predecessorRowId];
                            
                            // Now find the project_item_id for this work_item_id
                            if (isset($workItemToProjectItemMap[$predecessorWorkItemId])) {
                                $predecessorProjectItemId = $workItemToProjectItemMap[$predecessorWorkItemId];
                                ProjectItem::where('id', $currentProjectItemId)
                                    ->update(['predecessor' => $predecessorProjectItemId]);
                                
                                Log::info('Predecessor saved in template', [
                                    'current_work_item_id' => $item['work_item_id'],
                                    'predecessor_row_id' => $predecessorRowId,
                                    'predecessor_work_item_id' => $predecessorWorkItemId,
                                    'predecessor_project_item_id' => $predecessorProjectItemId,
                                    'current_project_item_id' => $currentProjectItemId
                                ]);
                            }
                        }
                    }
                }

                // ✅ حفظ المشاريع الفرعية
                if (!empty($subprojects) && is_array($subprojects)) {
                    $this->templateService->createSubprojects($template, $subprojects);
                }
            });

            return response()->json([
                'success' => true,
                'message' => __('general.template_saved_successfully')
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving template from form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ القالب: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeFromProject(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = \Modules\Progress\Models\Project::with('items.workItem')->findOrFail($validated['project_id']);

        $template = null;
        DB::transaction(function () use ($validated, $project, &$template) {
            $template = ProjectTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'status' => 'active',
                'project_type_id' => $project->project_type_id,
                'working_days' => $project->working_days,
                'daily_work_hours' => $project->daily_work_hours,
                'weekly_holidays' => $project->weekly_holidays,
                'working_zone' => $project->working_zone,
            ]);

            // Create mappings for predecessor conversion
            $itemMapping = []; // project_item_id => work_item_id
            $workItemMapping = []; // work_item_id => project_item_id
            
            foreach ($project->items as $item) {
                $itemMapping[$item->id] = $item->work_item_id;
                $workItemMapping[$item->work_item_id] = $item->id;
            }
            
            Log::info('Project item mappings created', [
                'project_id' => $project->id,
                'total_items' => count($itemMapping),
                'item_mapping' => $itemMapping
            ]);

            // First pass: Create all template items without predecessors in project_items table
            $templateItemMapping = []; // Maps work_item_id to project_item_id
            foreach ($project->items as $item) {
                $projectItem = ProjectItem::create([
                    'project_id' => null, // Template items don't have project_id
                    'project_template_id' => $template->id,
                    'work_item_id' => $item->work_item_id,
                    'item_label' => $item->item_label ?? null,
                    'total_quantity' => $item->total_quantity,
                    'completed_quantity' => 0, // Template items have no progress
                    'remaining_quantity' => $item->total_quantity,
                    'estimated_daily_qty' => $item->estimated_daily_qty,
                    'daily_quantity' => $item->daily_quantity ?? null,
                    'duration' => $item->duration,
                    'shift' => $item->shift ?? null,
                    'predecessor' => null, // Will be set in second pass
                    'dependency_type' => $item->dependency_type,
                    'lag' => $item->lag,
                    'notes' => $item->notes,
                    'item_order' => $item->item_order,
                    'subproject_name' => $item->subproject_name,
                    'start_date' => !empty($item->start_date) ? $item->start_date : now()->format('Y-m-d'),
                    'end_date' => !empty($item->end_date) ? $item->end_date : now()->addDays(30)->format('Y-m-d'),
                    'planned_end_date' => !empty($item->planned_end_date) ? $item->planned_end_date : (!empty($item->end_date) ? $item->end_date : now()->addDays(30)->format('Y-m-d')),
                    'is_measurable' => $item->is_measurable ?? false,
                ]);
                $templateItemMapping[$item->work_item_id] = $projectItem->id;
            }

            // Second pass: Update predecessors using project_item_id
            foreach ($project->items as $item) {
                if ($item->predecessor) {
                    $currentProjectItemId = $templateItemMapping[$item->work_item_id];
                    
                    // Get predecessor work_item_id from project item mapping
                    if (isset($itemMapping[$item->predecessor])) {
                        $predecessorWorkItemId = $itemMapping[$item->predecessor];
                        
                        // Verify that the predecessor work item exists in the template
                        if (isset($templateItemMapping[$predecessorWorkItemId])) {
                            $predecessorProjectItemId = $templateItemMapping[$predecessorWorkItemId];
                            
                            // Store project_item_id as predecessor
                            ProjectItem::where('id', $currentProjectItemId)
                                ->update(['predecessor' => $predecessorProjectItemId]);
                            
                            Log::info('Template predecessor set successfully', [
                                'current_work_item_id' => $item->work_item_id,
                                'predecessor_work_item_id' => $predecessorWorkItemId,
                                'predecessor_project_item_id' => $predecessorProjectItemId,
                                'project_predecessor_id' => $item->predecessor
                            ]);
                        } else {
                            Log::warning('Predecessor work item not in template', [
                                'current_work_item_id' => $item->work_item_id,
                                'predecessor_work_item_id' => $predecessorWorkItemId
                            ]);
                        }
                    } else {
                        Log::warning('Predecessor project item not found', [
                            'current_work_item_id' => $item->work_item_id,
                            'predecessor_project_item_id' => $item->predecessor
                        ]);
                    }
                }
            }

            // ✅ نسخ المشاريع الفرعية
            if ($project->subprojects && $project->subprojects->count() > 0) {
                foreach ($project->subprojects as $subproject) {
                    Subproject::create([
                        'project_template_id' => $template->id,
                        'name' => $subproject->name,
                        'start_date' => $subproject->start_date,
                        'end_date' => $subproject->end_date,
                        'total_quantity' => $subproject->total_quantity ?? 0,
                        'unit' => $subproject->unit,
                    ]);
                }
            }
        });

        if ($template && $template->id) {
            $createdTemplate = ProjectTemplate::with('items')->find($template->id);
            if ($createdTemplate) {
                $itemsWithPredecessors = $createdTemplate->items->where('predecessor', '!=', null)->count();
                
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء القالب بنجاح',
                    'debug' => [
                        'template_id' => $template->id,
                        'total_items' => $createdTemplate->items->count(),
                        'items_with_predecessors' => $itemsWithPredecessors
                    ]
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء القالب بنجاح'
        ]);
    }

    /**
     * ✅ إنشاء المشاريع الفرعية للقالب (مثل Projects)
     */
}
