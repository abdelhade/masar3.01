<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BarcodeController extends Controller
{
    // صلاحيات الباركود
    public function __construct()
    {
        $this->middleware('can:عرض - رموز الشريط')->only(['index']);
        $this->middleware('can:عرض - تفاصيل رمز شريط')->only(['show']);
        $this->middleware('can:إنشاء - رموز الشريط')->only(['create', 'store']);
        $this->middleware('can:تعديل - رموز الشريط')->only(['edit', 'update']);
        $this->middleware('can:حذف - رموز الشريط')->only(['destroy']);
    }

    // عرض جميع الرموز
    public function index()
    {
        $barcodes = Barcode::all(); // جلب جميع الرموز
        return view('barcodes.index', compact('barcodes')); // عرض البيانات في صفحة
    }

    // عرض نموذج إضافة رمز شريط جديد
    public function create()
    {
        return view('barcodes.create');
    }

    // تخزين رمز الشريط الجديد في قاعدة البيانات
    public function store(Request $request)
    {
        // التحقق من المدخلات
        $request->validate([
            'item_id' => 'required|integer',
            'barcode' => 'required|string|max:255|unique:barcodes,barcode',
            'isdeleted' => 'required|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
        ]);

        // إضافة رمز شريط جديد
        Barcode::create([
            'item_id' => $request->item_id,
            'barcode' => $request->barcode,
            'isdeleted' => $request->isdeleted,
            'tenant' => $request->tenant ?? 0,
            'branch' => $request->branch ?? 0,
        ]);

        return redirect()->route('barcodes.index')->with('success', 'Barcode added successfully');
    }

    // عرض تفاصيل رمز الشريط
    public function show($id)
    {
        $barcode = Barcode::findOrFail($id); // جلب رمز الشريط بناءً على ID
        return view('barcodes.show', compact('barcode')); // عرض التفاصيل
    }

    // عرض نموذج تعديل رمز الشريط
    public function edit($id)
    {
        $barcode = Barcode::findOrFail($id); // جلب رمز الشريط بناءً على ID
        return view('barcodes.edit', compact('barcode')); // عرض نموذج التعديل
    }

    // تحديث رمز الشريط في قاعدة البيانات
    public function update(Request $request, $id)
    {
        // التحقق من المدخلات
        $request->validate([
            'item_id' => 'required|integer',
            'barcode' => 'required|string|max:255|unique:barcodes,barcode,' . $id,
            'isdeleted' => 'required|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
        ]);

        $barcode = Barcode::findOrFail($id); // العثور على رمز الشريط بناءً على ID
        $barcode->update([
            'item_id' => $request->item_id,
            'barcode' => $request->barcode,
            'isdeleted' => $request->isdeleted,
            'tenant' => $request->tenant ?? 0,
            'branch' => $request->branch ?? 0,
        ]);

        return redirect()->route('barcodes.index')->with('success', 'Barcode updated successfully');
    }

    // حذف رمز الشريط من قاعدة البيانات
    public function destroy($id)
    {
        $barcode = Barcode::findOrFail($id); // العثور على رمز الشريط بناءً على ID
        $barcode->delete(); // حذف رمز الشريط

        return redirect()->route('barcodes.index')->with('success', 'Barcode deleted successfully');
    }
}
