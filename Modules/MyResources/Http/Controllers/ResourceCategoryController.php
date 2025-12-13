<?php

namespace Modules\MyResources\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\MyResources\Http\Requests\ResourceCategoryRequest;
use Modules\MyResources\Models\ResourceCategory;

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
        $data = $request->validated();
        $data['sort_order'] = (ResourceCategory::max('sort_order') ?? 0) + 1;

        ResourceCategory::create($data);

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

    public function show(ResourceCategory $category)
    {
        return view('myresources::categories.show', compact('category'));
    }

    public function destroy(ResourceCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('myresources.categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح');
    }
}
