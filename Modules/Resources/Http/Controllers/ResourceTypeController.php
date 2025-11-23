<?php

namespace Modules\MyResources\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Http\Requests\ResourceTypeRequest;

class ResourceTypeController extends Controller
{
    public function index()
    {
        $types = ResourceType::with('category')->get();
        $categories = ResourceCategory::active()->ordered()->get();

        return view('myresources::types.index', compact('types', 'categories'));
    }

    public function create()
    {
        $categories = ResourceCategory::active()->ordered()->get();

        return view('myresources::types.create', compact('categories'));
    }

    public function store(ResourceTypeRequest $request)
    {
        ResourceType::create($request->validated());

        return redirect()
            ->route('myresources.types.index')
            ->with('success', 'تم إضافة النوع بنجاح');
    }

    public function edit(ResourceType $type)
    {
        $categories = ResourceCategory::active()->ordered()->get();

        return view('myresources::types.edit', compact('type', 'categories'));
    }

    public function update(ResourceTypeRequest $request, ResourceType $type)
    {
        $type->update($request->validated());

        return redirect()
            ->route('myresources.types.index')
            ->with('success', 'تم تحديث النوع بنجاح');
    }

    public function destroy(ResourceType $type)
    {
        $type->delete();

        return redirect()
            ->route('myresources.types.index')
            ->with('success', 'تم حذف النوع بنجاح');
    }
}

