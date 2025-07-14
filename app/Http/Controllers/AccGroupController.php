<?php

namespace App\Http\Controllers;

use App\Models\AccGroup;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AccGroupController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:عرض إدارة الحسابات')->only(['index', 'show']);
        $this->middleware('can:إضافة إدارة الحسابات')->only(['create', 'store']);
        $this->middleware('can:تعديل إدارة الحسابات')->only(['edit', 'update']);
        $this->middleware('can:حذف إدارة الحسابات')->only(['destroy']);
        $this->middleware('can:طباعة إدارة الحسابات')->only(['print']);

        $this->middleware('can:عرض الحسابات')->only(['index', 'show']);
        $this->middleware('can:عرض حساب فرعي')->only(['showSubAccount']);
        $this->middleware('can:إضافة الحسابات')->only(['create', 'store']);
        $this->middleware('can:تعديل الحسابات')->only(['edit', 'update']);
        $this->middleware('can:حذف الحسابات')->only(['destroy']);
        $this->middleware('can:بحث الحسابات')->only(['search']);
    }

    // عرض جميع المجموعات
    public function index()
    {
        $accGroups = AccGroup::all(); // جلب جميع المجموعات
        return view('accGroups.index', compact('accGroups')); // عرض البيانات في صفحة
    }

    // عرض نموذج إضافة مجموعة جديدة
    public function create()
    {
        return view('accGroups.create');
    }

    // تخزين مجموعة جديدة في قاعدة البيانات
    public function store(Request $request)
    {
        // التحقق من المدخلات
        $request->validate([
            'name' => 'required|string|max:255|unique:acc_groups,name',
            'description' => 'nullable|string|max:500',
            'isdeleted' => 'required|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
        ]);

        // إضافة مجموعة جديدة
        AccGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'isdeleted' => $request->isdeleted,
            'tenant' => $request->tenant ?? 0,
            'branch' => $request->branch ?? 0,
        ]);

        return redirect()->route('accGroups.index')->with('success', 'Group added successfully');
    }

    // عرض تفاصيل مجموعة معينة
    public function show($id)
    {
        $accGroup = AccGroup::findOrFail($id); // جلب المجموعة بناءً على ID
        return view('accGroups.show', compact('accGroup')); // عرض التفاصيل
    }

    // عرض نموذج تعديل مجموعة معينة
    public function edit($id)
    {
        $accGroup = AccGroup::findOrFail($id); // العثور على المجموعة بناءً على ID
        return view('accGroups.edit', compact('accGroup')); // عرض نموذج التعديل
    }

    // تحديث مجموعة معينة في قاعدة البيانات
    public function update(Request $request, $id)
    {
        // التحقق من المدخلات
        $request->validate([
            'name' => 'required|string|max:255|unique:acc_groups,name,' . $id,
            'description' => 'nullable|string|max:500',
            'isdeleted' => 'required|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
        ]);

        $accGroup = AccGroup::findOrFail($id); // العثور على المجموعة بناءً على ID
        $accGroup->update([
            'name' => $request->name,
            'description' => $request->description,
            'isdeleted' => $request->isdeleted,
            'tenant' => $request->tenant ?? 0,
            'branch' => $request->branch ?? 0,
        ]);

        return redirect()->route('accGroups.index')->with('success', 'Group updated successfully');
    }

    // حذف مجموعة معينة من قاعدة البيانات
    public function destroy($id)
    {
        $accGroup = AccGroup::findOrFail($id); // العثور على المجموعة بناءً على ID
        $accGroup->delete(); // حذف المجموعة

        return redirect()->route('accGroups.index')->with('success', 'Group deleted successfully');
    }
}
