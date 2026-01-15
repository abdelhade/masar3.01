<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Employee;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\ProjectProgress;
use Modules\Progress\Models\ProjectItem;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function __construct()
    {
        
        $this->middleware('can:view progress-projects')->only('create', 'store');
    }       

    public function create()
    {
        $clients = Client::all();
        $projectTypes = ProjectType::all();
        $employees = Employee::all();
        $workItems = WorkItem::all(); // Consider AJAX for large datasets
        $templates = ProjectTemplate::with('items.workItem')->get();

        return view('progress::projects.create', compact('clients', 'projectTypes', 'employees', 'workItems', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'start_date' => 'required|date',
            'items' => 'required|array',
            'items.*.work_item_id' => 'required|exists:work_items,id',
            'items.*.subproject_name' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string',
            'items.*.is_measurable' => 'nullable|boolean',
            'items.*.total_quantity' => 'required|numeric|min:0',
            'items.*.estimated_daily_qty' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            // Helper to calculate end date (simplified logic, real logic might need holidays)
            // Implementation in JS is for display, here we might double check or trust JS inputs for dates 
            // BUT standard practice is to recalculate or validate. 
            // For now, we trust the dates sent from frontend or recalculate if needed.
            // The prompt implies "Save Items" logic.

            $project = ProjectProgress::create([
                'name' => $request->name,
                'client_id' => $request->client_id,
                'status' => 'pending', // Default
                'start_date' => $request->start_date,
                // 'end_date' => calculated or from request,
                'description' => $request->description,
                'project_type_id' => $request->project_type_id,
                'working_days' => $request->working_days ?? 5,
                'daily_work_hours' => $request->daily_work_hours ?? 8,
                'weekly_holidays' => $request->weekly_holidays, // Saved as string "5,6"
                'working_zone' => $request->working_zone,
            ]);

            // Attach Employees
            if ($request->has('employees')) {
                $project->employees()->attach($request->employees);
            }

            // Save Items
            // Save Items
            $itemMapping = []; // Maps input index (0, 1, 2) to Real DB ID
            $createdItems = [];

            if ($request->has('items')) {
                // Step 1: Create all items first to generate IDs
                foreach ($request->items as $index => $itemData) {
                    $createdItem = $project->items()->create([
                        'work_item_id' => $itemData['work_item_id'],
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
                    ]);
                    
                    // Store mapping: Input Index => Real ID
                    $itemMapping[$index] = $createdItem->id;
                    $createdItems[$index] = $createdItem;
                }

                // Step 2: Update predecessors using the mapping
                foreach ($request->items as $index => $itemData) {
                    if (isset($itemData['predecessor']) && $itemData['predecessor'] !== '' && $itemData['predecessor'] !== null) {
                        $predIndex = $itemData['predecessor'];
                        
                        // Check if predecessor index exists in our mapping
                        if (isset($itemMapping[$predIndex])) {
                            $realPredId = $itemMapping[$predIndex];
                            $currentItem = $createdItems[$index];
                            
                            $currentItem->update([
                                'predecessor' => $realPredId
                            ]);
                        }
                    }
                }
            }
            
            // Calculate Project End Date based on last item end date
            $lastEndDate = $project->items()->max('end_date');
            $project->update(['end_date' => $lastEndDate ?? $request->start_date]);

            DB::commit();

            return redirect()->route('progress.project.index')->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating project: ' . $e->getMessage()])->withInput();
        }
    }
}
