<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Modules\CRM\Enums\{TaskPriorityEnum, TaskStatusEnum};
use Modules\CRM\Http\Requests\TaskRequest;
use Modules\CRM\Models\{Task, TaskType};
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Tasks')->only(['index']);
        $this->middleware('can:create Tasks')->only(['create', 'store']);
        $this->middleware('can:edit Tasks')->only(['edit', 'update']);
        $this->middleware('can:delete Tasks')->only(['destroy']);
    }

    public function index()
    {
        $tasks = Task::with(['client', 'user', 'media'])->latest()->paginate(10);
        return view('crm::tasks.index', compact('tasks'));
    }

    public function create()
    {
        $branches = userBranches();
        $taskTypes = TaskType::pluck('title', 'id');
        $users = User::pluck('name', 'id');
        $priorities = TaskPriorityEnum::cases();
        $statuses = TaskStatusEnum::cases();

        return view('crm::tasks.create',  get_defined_vars());
    }

    public function store(TaskRequest $request)
    {
        // dd($request->all());
        $data = $request->validated();

        $task = Task::create($data);

        if ($request->hasFile('attachment')) {
            $task
                ->addMedia($request->file('attachment'))
                ->toMediaCollection('tasks');
        }

        Alert::toast(__('Task created successfully'), 'success');
        return redirect()->route('tasks.index');


        return redirect()->route('tasks.index');
    }
    public function show($id)
    {
        // return view('crm::show');
    }
    public function edit(Task $task)
    {
        $taskTypes = TaskType::pluck('title', 'id');
        $users = User::pluck('name', 'id');
        $priorities = TaskPriorityEnum::cases();
        $statuses = TaskStatusEnum::cases();

        return view('crm::tasks.edit', compact('task', 'taskTypes', 'users', 'priorities', 'statuses'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        try {
            $task->update($request->validated());

            if ($request->hasFile('attachment')) {
                $task->clearMediaCollection('attachments');
                $task->addMediaFromRequest('attachment')->toMediaCollection('attachments');
            }

            Alert::toast(__('Task updated successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while updating the task'), 'error');
        }

        return redirect()->route('tasks.index');
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();
            Alert::toast(__('Task deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the task'), 'error');
        }

        return redirect()->route('tasks.index');
    }

    public function kanban()
    {
        $tasks = Task::with(['client', 'user'])->get();
        $statuses = TaskStatusEnum::cases();

        return view('crm::tasks.kanban', compact('tasks', 'statuses'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'status' => 'required',
        ]);

        $task = Task::findOrFail($request->task_id);
        $task->status = $request->status;
        $task->save();

        return response()->json(['success' => true, 'message' => __('Task status updated successfully')]);
    }
}
