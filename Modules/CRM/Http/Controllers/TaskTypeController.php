<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Http\Requests\TaskTypeRequest;
use Modules\CRM\Models\TaskType;
use RealRashid\SweetAlert\Facades\Alert;

class TaskTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Task Types')->only(['index']);
        $this->middleware('can:create Task Types')->only(['create', 'store']);
        $this->middleware('can:edit Task Types')->only(['edit', 'update']);
        $this->middleware('can:delete Task Types')->only(['destroy']);
    }

    public function index()
    {
        $taskType = TaskType::all();
        return view('crm::task-types.index', compact('taskType'));
    }

    public function create()
    {
        return view('crm::task-types.create');
    }

    public function store(TaskTypeRequest $request)
    {
        TaskType::create($request->validated());
        Alert::toast(__('Task type created successfully'), 'success');
        return redirect()->route('tasks.types.index');
    }

    public function edit($id)
    {
        $taskType = TaskType::findOrFail($id);
        return view('crm::task-types.edit', compact('taskType'));
    }

    public function update(TaskTypeRequest $request, $id)
    {
        $taskType = TaskType::findOrFail($id);
        $taskType->update($request->validated());
        Alert::toast(__('Task type updated successfully'), 'success');
        return redirect()->route('tasks.types.index');
    }

    public function destroy($id)
    {
        try {
            $taskType = TaskType::findOrFail($id);
            $taskType->delete();
            Alert::toast(__('Task type deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the task type'), 'error');
        }
        return redirect()->route('tasks.types.index');
    }
}
