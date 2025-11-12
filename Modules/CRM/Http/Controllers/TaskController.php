<?php

namespace Modules\CRM\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Routing\Controller;
use Modules\CRM\Models\{Task, TaskType};
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\TaskRequest;
use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};

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
        $tasks = Task::with(['client', 'user', 'media'])
            ->latest()
            ->paginate(10);
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
        try {
            $data = $request->validated();

            $task = Task::create($data);

            if ($request->hasFile('attachment')) {
                $task
                    ->addMedia($request->file('attachment'))
                    ->toMediaCollection('tasks');
            }
            Alert::toast('تم الانشاء بنجاح', 'success');
            return redirect()->route('tasks.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ', 'error');
            return redirect()->route('tasks.index');
        }
    }

    public function show($id)
    {
        // return view('crm::show');
    }

    public function edit(Task $task)
    {
        $taskTypes = TaskType::pluck('title', 'id');
        $users = User::pluck('name', 'id');
        return view('crm::tasks.edit', get_defined_vars());
    }

    public function update(TaskRequest $request, Task $task)
    {
        try {
            $task->update([
                'client_id' => $request->client_id,
                'user_id' => $request->user_id,
                'type' => $request->type,
                'title' => $request->title,
                'status' => $request->status,
                'priority' => $request->priority,
                'delivery_date' => $request->delivery_date,
                'client_comment' => $request->client_comment,
                'user_comment' => $request->user_comment,
            ]);

            if ($request->hasFile('attachment')) {
                $task->clearMediaCollection('attachments');
                $task->addMediaFromRequest('attachment')->toMediaCollection('attachments');
            }
            Alert::toast('تم تحديث المهمة بنجاح', 'success');
            return redirect()->route('tasks.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ', 'error');
            return redirect()->route('tasks.index');
        }
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        if ($task->media->count() > 0) {
            $task->media()->delete();
        }
        $task->delete();
        Alert::toast('تم الحذف بنجاح', 'success');
        return redirect()->route('tasks.index');
    }
}
