<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Routing\Controller;
use Exception;
use Modules\Progress\Http\Requests\WorkItemRequest;
use Modules\Progress\Models\WorkItem;
use RealRashid\SweetAlert\Facades\Alert;

use Illuminate\Http\Request;

class WorkItemController extends Controller
{
        public function __construct()
    {
        $this->middleware('can:view progress-work-items')->only(['index','show']);
        $this->middleware('can:create progress-work-items')->only(['create', 'store']);
        $this->middleware('can:edit progress-work-items')->only(['edit', 'update']);
        $this->middleware('can:delete progress-work-items')->only('destroy');
    }
    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:work_items,id',
        ]);

        foreach ($request->ids as $index => $id) {
            WorkItem::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function index()
    {
        $query = WorkItem::query()->orderBy('order', 'asc')->orderBy('created_at', 'desc');

        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        if (request('search')) {
            $searchTerm = request('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('unit', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function ($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $perPage = request('per_page', 20);
        $workItems = $query->paginate($perPage);
        $categories = \Modules\Progress\Models\WorkItemCategory::all();

        return view('progress::work-items.index', compact('workItems', 'categories'));
    }

    public function create()
    {
        $categories = \Modules\Progress\Models\WorkItemCategory::all();
        return view('progress::work-items.create', compact('categories'));
    }

    public function store(WorkItemRequest $request)
    {
        try {
            WorkItem::create($request->validated());
            Alert::toast('تم الاضافه بنجاح', 'success');

            return redirect()->route('work.items.index');
        } catch (Exception) {
            Alert::toast('حدث خطا', 'error');

            return redirect()->route('work.items.index');
        }
    }

    public function edit(WorkItem $workItem)
    {
        $categories = \Modules\Progress\Models\WorkItemCategory::all();
        return view('progress::work-items.edit', compact('workItem', 'categories'));
    }

    public function update(WorkItemRequest $request, WorkItem $workItem)
    {
        try {
            $workItem->update($request->validated());
            Alert::toast('تم التعديل بنجاح', 'success');

            return redirect()->route('work.items.index');
        } catch (Exception) {
            Alert::toast('حدث خطا', 'error');

            return redirect()->route('work.items.index');
        }
    }

    public function show(WorkItem $workItem)
    {
        return view('progress::work-items.show', compact('workItem'));
    }

    public function destroy(WorkItem $workItem)
    {
        try {
            $workItem->delete();
            Alert::toast('تم الحذف بنجاح', 'success');

            return redirect()->route('work.items.index');
        } catch (Exception) {
            Alert::toast('حدث خطا', 'error');

            return redirect()->route('work.items.index');
        }
    }
}
