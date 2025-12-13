<?php

namespace Modules\MyResources\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\MyResources\Http\Requests\ResourceStatusRequest;
use Modules\MyResources\Models\ResourceStatus;

class ResourceStatusController extends Controller
{
    public function index()
    {
        $statuses = ResourceStatus::ordered()->get();

        return view('myresources::statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('myresources::statuses.create');
    }

    public function store(ResourceStatusRequest $request)
    {
        $data = $request->validated();
        $data['sort_order'] = (ResourceStatus::max('sort_order') ?? 0) + 1;

        ResourceStatus::create($data);

        return redirect()
            ->route('myresources.statuses.index')
            ->with('success', 'تم إضافة الحالة بنجاح');
    }

    public function edit(ResourceStatus $status)
    {
        return view('myresources::statuses.edit', compact('status'));
    }

    public function update(ResourceStatusRequest $request, ResourceStatus $status)
    {
        $status->update($request->validated());

        return redirect()
            ->route('myresources.statuses.index')
            ->with('success', 'تم تحديث الحالة بنجاح');
    }

    public function show(ResourceStatus $status)
    {
        return view('myresources::statuses.show', compact('status'));
    }

    public function destroy(ResourceStatus $status)
    {
        $status->delete();

        return redirect()
            ->route('myresources.statuses.index')
            ->with('success', 'تم حذف الحالة بنجاح');
    }
}
