<?php

namespace Modules\MyResources\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Http\Requests\ResourceCategoryRequest;

class ResourceCategoryController extends Controller
{
    public function index()
    {
        $categories = ResourceCategory::ordered()->get();

        return view('myresources::categories.index', compact('categories'));
    }

    public function create()
    {
        return view('myresources::categories.create');
    }

    public function store(ResourceCategoryRequest $request)
    {
        ResourceCategory::create($request->validated());

        return redirect()
            ->route('myresources.categories.index')
            ->with('success', 'تم إضافة التصنيف بنجاح');
    }

    public function edit(ResourceCategory $category)
    {
        return view('myresources::categories.edit', compact('category'));
    }

    public function update(ResourceCategoryRequest $request, ResourceCategory $category)
    {
        $category->update($request->validated());

        return redirect()
            ->route('myresources.categories.index')
            ->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy(ResourceCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('myresources.categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح');
    }
}

