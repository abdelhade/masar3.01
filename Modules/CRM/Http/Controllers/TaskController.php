<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\{User, Client};
use Modules\CRM\Models\{Task, TaskType};
use App\Http\Controllers\Controller;
use Modules\CRM\Enums\{TaskStatusEnum, TaskPriorityEnum};
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\TaskRequest;
use Exception;

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
        $taskTypes = TaskType::pluck('title', 'id');

        $clients = Client::pluck('cname', 'id');
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
        $clients = Client::pluck('cname', 'id');
        $users = User::pluck('name', 'id');
        return view('crm::tasks.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     */
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
