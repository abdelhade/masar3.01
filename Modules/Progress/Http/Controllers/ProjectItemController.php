<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\ProjectProgress as Project;
use Illuminate\Http\Request;

class ProjectItemController extends Controller
{
    public function __construct()
{
    $this->middleware('can:view progress-work-items')->only('index');
    $this->middleware('can:create progress-work-items')->only(['create', 'store']);
    $this->middleware('can:edit progress-work-items')->only(['edit', 'update']);
    $this->middleware('can:delete progress-work-items')->only('destroy');
}
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'work_item_id' => 'required|exists:work_items,id',
            'total_quantity' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $project->items()->create([
            'work_item_id' => $request->work_item_id,
            'total_quantity' => $request->total_quantity,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'daily_quantity' => $this->calculateDailyQuantity(
                $request->total_quantity,
                $request->start_date,
                $request->end_date
            )
        ]);

        return back()->with('success', 'تم إضافة بند العمل للمشروع بنجاح');
    }

    public function update(Request $request, ProjectItem $projectItem)
    {
        $request->validate([
            'total_quantity' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $projectItem->update([
            'total_quantity' => $request->total_quantity,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'daily_quantity' => $this->calculateDailyQuantity(
                $request->total_quantity,
                $request->start_date,
                $request->end_date
            )
        ]);

        return back()->with('success', 'تم تحديث بند العمل بنجاح');
    }

    public function destroy(ProjectItem $projectItem)
    {
        $projectItem->delete();
        return back()->with('success', 'تم حذف بند العمل بنجاح');
    }

    public function getByProject($projectId)
    {
        $today = now()->format('Y-m-d');
        
        $items = ProjectItem::with('workItem.category')
            ->where('project_id', $projectId)
            ->get()
            ->map(function ($item) use ($today) {
                // Get today's executed quantity
                $todayQuantity = \Modules\Progress\Models\DailyProgress::where('project_item_id', $item->id)
                    ->whereDate('progress_date', $today)
                    ->whereNull('deleted_at')
                    ->sum('quantity');
                
                // Add today's quantity to the item
                $itemArray = $item->toArray();
                $itemArray['today_executed_quantity'] = $todayQuantity;
                $itemArray['is_measurable'] = $item->is_measurable ?? false;
                
                return $itemArray;
            });
        
        return response()->json($items);
    }

    private function calculateDailyQuantity($total, $start, $end)
    {
        $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
        return round($total / $days, 2);
    }
}
