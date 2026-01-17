<?php

namespace Modules\Progress\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Progress\Models\WorkItem;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\Subproject;
use Modules\Progress\Http\Requests\ProjectTemplateRequest;

class ProjectTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-project-templates')->only(['index','show']);
        $this->middleware('can:create progress-project-templates')->only(['create', 'store']);
        $this->middleware('can:edit progress-project-templates')->only(['edit', 'update']);
        $this->middleware('can:delete progress-project-templates')->only('destroy');
    }
    public function index()
    {
        $templates = ProjectTemplate::with('projectType')->withCount('items')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('progress::project-templates.index', compact('templates'));
    }

    public function create()
    {
        $workItems = WorkItem::all();
        $projectTypes = ProjectType::all();
        $subprojects = Subproject::distinct()->pluck('name');
        return view('progress::project-templates.create', compact('workItems', 'projectTypes', 'subprojects'));
    }

    public function store(ProjectTemplateRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // 1. Create Template
                $template = ProjectTemplate::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'project_type_id' => $request->project_type_id,
                    'weekly_holidays' => $request->weekly_holidays, // Casted to array in Model
                ]);

                // 2. Process Items
                $createdItems = []; // Map: Index => ProjectItem
                $subprojectMap = []; // Map: Name => Subproject ID

                foreach ($request->items as $index => $itemData) {
                    
                    // Handle Subproject
                    $subprojectId = null;
                    if (!empty($itemData['subproject_name'])) {
                        $spName = trim($itemData['subproject_name']);
                        
                        if (!isset($subprojectMap[$spName])) {
                            // Check if exists for this template (unlikely as we just created template, but good practice)
                            // or usually just create new, as templates are isolated.
                            $subproject = Subproject::create([
                                'name' => $spName,
                                'project_template_id' => $template->id,
                            ]);
                            $subprojectMap[$spName] = $subproject->id;
                        }
                        $subprojectId = $subprojectMap[$spName];
                    }

                    // Create Project Item
                    $projectItem = ProjectItem::create([
                        'project_template_id' => $template->id,
                        'work_item_id' => $itemData['work_item_id'],
                        'item_order' => $itemData['item_order'] ?? $index,
                        'subproject_name' => $itemData['subproject_name'] ?? null,
                        'subproject_id' => $subprojectId,
                        'notes' => $itemData['notes'] ?? null,
                        'is_measurable' => isset($itemData['is_measurable']) ? 1 : 0,
                        'total_quantity' => $itemData['default_quantity'] ?? 0,
                        'estimated_daily_qty' => $itemData['estimated_daily_qty'] ?? 0,
                        'duration' => $itemData['duration'] ?? 0,
                        'dependency_type' => $itemData['dependency_type'] ?? null,
                        'lag' => $itemData['lag'] ?? 0,
                        'start_date' => $itemData['start_date'] ?? null,
                        'end_date' => $itemData['end_date'] ?? null,
                    ]);

                    $createdItems[$index] = $projectItem;
                }

                // 3. Second Pass for Predecessors
                // We assume 'predecessor' input contains the INDEX of the referenced row
                foreach ($request->items as $index => $itemData) {
                    if (isset($itemData['predecessor']) && $itemData['predecessor'] !== '') {
                        $predIndex = $itemData['predecessor'];
                        if (isset($createdItems[$predIndex])) {
                            $currentItem = $createdItems[$index];
                            $currentItem->update([
                                'predecessor' => $createdItems[$predIndex]->id
                            ]);
                        }
                    }
                }
            });

            Alert::toast('تم إنشاء القالب والبنود بنجاح', 'success');
            return redirect()->route('project.template.index');
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Template Store Error: ' . $e->getMessage());
            Alert::toast('حدث خطأ أثناء الإضافة: ' . $e->getMessage(), 'error');
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(ProjectTemplate $projectTemplate)
    {
        $projectTemplate->load('items.workItem', 'subprojects');
        return view('progress::project-templates.show', compact('projectTemplate'));
    }

    public function edit(ProjectTemplate $project_template)
    {
        $project_template->load('items');
        $workItems = WorkItem::all();
        $projectTypes = ProjectType::all();
        
        // Prepare valid initial items JSON including all fields
        $initialItems = $project_template->items->map(function ($item) {
             return $item;
        })->values()->toArray();

        $subprojects = Subproject::distinct()->pluck('name');

        return view('progress::project-templates.edit', [
            'projectTemplate' => $project_template,
            'initialItems' => json_encode($initialItems),
            'workItems' => $workItems,
            'projectTypes' => $projectTypes,
            'subprojects' => $subprojects,
        ]);
    }

    public function update(ProjectTemplateRequest $request, ProjectTemplate $project_template)
    {
        // ... (Similar logic update required, but store is priority)
        // Leaving simplified for now as user focused on "Data Flow" of creation mostly.
        try {
             DB::transaction(function () use ($request, $project_template) {
                \Illuminate\Support\Facades\Log::info('Update Template Request', ['items' => $request->items]);
                $project_template->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'project_type_id' => $request->project_type_id,
                    'weekly_holidays' => $request->weekly_holidays,
                ]);

                // Full rebuild of items is often easiest for templates if IDs don't matter much contextually
                // But if we want to preserve IDs, it's harder.
                // Given the instructions, let's do a delete-and-recreate for simplicity OR simple update
                // The previous code had update logic.
                
                // For this task, I will stick to basic update or just redirect to index as "Not Implemented Full Update Yet" 
                // but better to allow basic update of main fields.
                // Re-implementing full logic for Update is safe.
                 
                $project_template->items()->delete();
                $project_template->subprojects()->delete();
                
                 // Re-run store logic basically (Copy-paste logic Refactor)
                $createdItems = []; 
                $subprojectMap = []; 

                foreach ($request->items as $index => $itemData) {
                    $subprojectId = null;
                    if (!empty($itemData['subproject_name'])) {
                        $spName = trim($itemData['subproject_name']);
                        if (!isset($subprojectMap[$spName])) {
                            $subproject = Subproject::create([
                                'name' => $spName,
                                'project_template_id' => $project_template->id,
                            ]);
                            $subprojectMap[$spName] = $subproject->id;
                        }
                        $subprojectId = $subprojectMap[$spName];
                    }

                    $projectItem = ProjectItem::create([
                        'project_template_id' => $project_template->id,
                        'work_item_id' => $itemData['work_item_id'],
                        'item_order' => $itemData['item_order'] ?? $index,
                        'subproject_name' => $itemData['subproject_name'] ?? null,
                        'subproject_id' => $subprojectId,
                        'notes' => $itemData['notes'] ?? null,
                        'is_measurable' => isset($itemData['is_measurable']) ? 1 : 0,
                        'total_quantity' => $itemData['default_quantity'] ?? 0,
                        'estimated_daily_qty' => $itemData['estimated_daily_qty'] ?? 0,
                        'duration' => $itemData['duration'] ?? 0,
                        'dependency_type' => $itemData['dependency_type'] ?? null,
                        'lag' => $itemData['lag'] ?? 0,
                        'start_date' => $itemData['start_date'] ?? null,
                        'end_date' => $itemData['end_date'] ?? null,
                    ]);
                    $createdItems[$index] = $projectItem;
                }

                foreach ($request->items as $index => $itemData) {
                    if (isset($itemData['predecessor']) && $itemData['predecessor'] !== '') {
                        $predIndex = $itemData['predecessor'];
                        \Illuminate\Support\Facades\Log::info("Processing Pred: Index $index points to PredIndex $predIndex");
                        if (isset($createdItems[$predIndex])) {
                            $createdItems[$index]->update([
                                'predecessor' => $createdItems[$predIndex]->id
                            ]);
                            \Illuminate\Support\Facades\Log::info("Updated Item {$createdItems[$index]->id} with PredID {$createdItems[$predIndex]->id}");
                        } else {
                            \Illuminate\Support\Facades\Log::warning("PredIndex $predIndex not found in createdItems");
                        }
                    }
                }

             });
            Alert::toast('تم تحديث القالب بنجاح', 'success');
            return redirect()->route('project.template.index');
        } catch (Exception $e) {
            Alert::toast('حدث خطأ أثناء التعديل', 'error');
            return redirect()->back();
        }
    }

    public function destroy(ProjectTemplate $project_template)
    {
        try {
            $project_template->delete();
            Alert::toast('تم حذف القالب بنجاح', 'success');
            return redirect()->route('project.template.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء الحذف', 'error');
            return redirect()->route('project.template.index');
        }
    }
}
