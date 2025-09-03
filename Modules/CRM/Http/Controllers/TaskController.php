<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Modules\CRM\Models\Task;
use Modules\CRM\Models\TaskType;
use Modules\CRM\Models\CrmClient;
use App\Http\Controllers\Controller;
use Modules\CRM\Enums\TaskStatusEnum;
use Modules\CRM\Enums\TaskPriorityEnum;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\TaskRequest;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with(['client', 'user', 'media'])
            ->latest()
            ->paginate(10);
        return view('crm::tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // في create و edit
        $taskTypes = TaskType::pluck('title', 'id');

        $clients = CrmClient::pluck('name', 'id');
        $users = User::pluck('name', 'id');

        $priorities = TaskPriorityEnum::cases();
        $statuses = TaskStatusEnum::cases();

        return view('crm::tasks.create', compact(
            'clients',
            'users',
            'priorities',
            'statuses',
            'taskTypes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        $task = Task::create($data);

        if ($request->hasFile('attachment')) {
            $task
                ->addMedia($request->file('attachment'))
                ->toMediaCollection('tasks');
        }
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('tasks.index');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $taskTypes = TaskType::pluck('title', 'id');
        $clients = CrmClient::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        return view('crm::tasks.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        // تحديث الحقول العادية
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

        // لو فيه مرفق جديد يتم رفعه ومسحه القديم
        if ($request->hasFile('attachment')) {
            $task->clearMediaCollection('attachments');
            $task->addMediaFromRequest('attachment')->toMediaCollection('attachments');
        }

        return redirect()->route('tasks.index')->with('success', 'تم تحديث المهمة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
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
