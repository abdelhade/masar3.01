<?php

namespace Modules\Progress\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use Modules\Progress\Models\WorkItem;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Progress\Http\Requests\WorkItemRequest;

class WorkItemController extends Controller
{
    public function index()
    {
        $workItems = WorkItem::paginate(20);
        return view('progress::work-items.index', compact('workItems'));
    }

    public function create()
    {
        return view('progress::work-items.create');
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
        return view('progress::work-items.edit', compact('workItem'));
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
