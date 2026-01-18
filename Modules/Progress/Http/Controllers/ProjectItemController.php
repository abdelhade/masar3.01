<?php

namespace Modules\Progress\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Modules\Progress\Models\ProjectItem;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Progress\Http\Requests\ProjectItemRequest;
use Modules\Progress\Models\ProjectProgress;
use Illuminate\Http\Request;

class ProjectItemController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:items-list')->only('index');
    //     $this->middleware('can:items-create')->only(['create', 'store']);
    //     $this->middleware('can:items-edit')->only(['edit', 'update']);
    //     $this->middleware('can:items-delete')->only('destroy');
    // }

    public function store(ProjectItemRequest $request, ProjectProgress $project)
    {
        try {
            $project->items()->create([
                'work_item_id'   => $request->work_item_id,
                'total_quantity' => $request->total_quantity,
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'daily_quantity' => $this->calculateDailyQuantity(
                    $request->total_quantity,
                    $request->start_date,
                    $request->end_date
                )
            ]);

            Alert::toast('تم إضافة بند العمل للمشروع بنجاح', 'success');
            return back();
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء الإضافة', 'error');
            return back();
        }
    }

    public function update(ProjectItemRequest $request, ProjectItem $projectItem)
    {
        try {
            $projectItem->update([
                'total_quantity' => $request->total_quantity,
                'start_date'     => $request->start_date,
                'end_date'       => $request->end_date,
                'daily_quantity' => $this->calculateDailyQuantity(
                    $request->total_quantity,
                    $request->start_date,
                    $request->end_date
                )
            ]);

            Alert::toast('تم تحديث بند العمل بنجاح', 'success');
            return back();
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء التحديث', 'error');
            return back();
        }
    }

    public function destroy(ProjectItem $projectItem)
    {
        try {
            $projectItem->delete();
            Alert::toast('تم حذف بند العمل بنجاح', 'success');
            return back();
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء الحذف', 'error');
            return back();
        }
    }

    public function apiIndex($projectId)
    {
        $items = ProjectItem::with(['workItem.category'])
            ->where('project_id', $projectId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'work_item' => [
                        'name' => $item->workItem->name ?? 'Unknown',
                        'unit' => $item->workItem->unit ?? '-',
                        'category' => $item->workItem->category->name ?? 'General',
                    ],
                    'subproject_name' => $item->subproject_name,
                    'is_measurable' => (bool)$item->is_measurable,
                    'daily_quantity' => $item->daily_quantity,
                    'notes' => $item->notes,
                    'total_quantity' => $item->total_quantity,
                    'completed_quantity' => $item->completed_quantity,
                    'completion_percentage' => $item->completion_percentage,
                ];
            });

        return response()->json($items);
    }

    public function updateItemStatus(Request $request, $project, $projectItem)
    {
        try {
            // Resolve IDs whether they are passed as Models (Binding) or IDs (String/Int)
            $projectId = is_object($project) ? $project->id : $project;
            $itemId = is_object($projectItem) ? $projectItem->id : $projectItem;

            // 1. Validation
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'item_status_id' => 'nullable|integer|exists:item_statuses,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            // 2. Find Item
            // We search for the item ensuring it belongs to the project
            $item = ProjectItem::where('id', $itemId)->where('project_id', $projectId)->first();

            if (!$item) {
                // Fallback: Check if item exists at all
                $item = ProjectItem::find($itemId);
                if (!$item) {
                     return response()->json(['success' => false, 'message' => 'Item not found'], 404);
                }
                
                // If item exists but project_id mismatch
                if ((int)$item->project_id !== (int)$projectId) {
                    return response()->json(['success' => false, 'message' => 'Item does not belong to the specified project'], 403);
                }
            }

            // 3. Update Status
            $statusId = $request->item_status_id ? (int)$request->item_status_id : null;
            
            $item->update([
                'item_status_id' => $statusId
            ]);
            
            return response()->json([
                'success' => true,
                'message' => __('general.updated_successfully')
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    private function calculateDailyQuantity($total, $start, $end)
    {
        $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
        return round($total / $days, 2);
    }
}
