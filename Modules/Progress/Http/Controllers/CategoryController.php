<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\WorkItemCategory as Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-categories')->only(['index']);
        $this->middleware('can:create progress-categories')->only(['create', 'store']);
        $this->middleware('can:edit progress-categories')->only(['edit', 'update']);
        $this->middleware('can:delete progress-categories')->only(['destroy']);
    }

    // عرض كل الفئات
    public function index()
    {
        $categories = Category::all();
        return view('progress::categories.index', compact('categories'));
    }

    // صفحة إنشاء فئة جديدة
    public function create()
    {
        return view('progress::categories.create');
    }

    // حفظ فئة جديدة في الداتابيز
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $category = \Modules\Progress\Models\WorkItemCategory::create([
        'name' => $request->name,
    ]);

    return redirect()->route('categories.index')->with('success','category created successfully.');
}



    // صفحة تعديل الفئة
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('progress::categories.edit', compact('category'));
    }

    // تحديث الفئة في الداتابيز
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    // حذف الفئة
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
