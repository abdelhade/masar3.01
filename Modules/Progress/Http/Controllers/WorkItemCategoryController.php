<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Progress\Models\WorkItemCategory;

class WorkItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

            public function __construct()
    {
        $this->middleware('can:view progress-work-item-categories')->only(['index','show']);
        $this->middleware('can:create progress-work-item-categories')->only(['create', 'store']);
        $this->middleware('can:edit progress-work-item-categories')->only(['edit', 'update']);
        $this->middleware('can:delete progress-work-item-categories')->only('destroy');
    }
    public function index()
    {
        $categories = WorkItemCategory::latest()->get();
        return view('progress::work-item-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('progress::work-item-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:work_item_categories,name',
        ]);

        WorkItemCategory::create($request->only('name'));

        return redirect()->route('work-item-categories.index')
            ->with('success', __('general.created_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = WorkItemCategory::findOrFail($id);
        return view('progress::work-item-categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:work_item_categories,name,' . $id,
        ]);

        $category = WorkItemCategory::findOrFail($id);
        $category->update($request->only('name'));

        return redirect()->route('work-item-categories.index')
            ->with('success', __('general.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = WorkItemCategory::findOrFail($id);
        
        // Optional: Check if used in working items before delete?
        // For now, allow delete (or rely on DB constraints/software delete if needed)
        
        $category->delete();

        return redirect()->route('work-item-categories.index')
            ->with('success', __('general.deleted_successfully'));
    }
}
