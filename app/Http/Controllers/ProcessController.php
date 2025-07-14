<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProcessController extends Controller
{
    // عرض جميع العمليات
    public function index()
    {
        $processes = Process::all(); // استرجاع جميع العمليات من الجدول
        return view('process.index', compact('processes'));
    }

    // عرض نموذج إضافة عملية جديدة
    public function create()
    {
        return view('process.create');
    }

    // حفظ عملية جديدة في قاعدة البيانات
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        Process::create($validated); // إضافة العملية إلى قاعدة البيانات

        return redirect()->route('process.index')->with('success', 'تم إضافة العملية بنجاح');
    }

    // عرض نموذج تعديل عملية موجودة
    public function edit($id)
    {
        $process = Process::findOrFail($id); // جلب العملية باستخدام الـ ID
        return view('process.edit', compact('process'));
    }

    // تحديث العملية في قاعدة البيانات
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        $process = Process::findOrFail($id);
        $process->update($validated); // تحديث العملية

        return redirect()->route('process.index')->with('success', 'تم تحديث العملية بنجاح');
    }

    // حذف عملية من قاعدة البيانات
    public function destroy($id)
    {
        $process = Process::findOrFail($id);
        $process->delete(); // حذف العملية

        return redirect()->route('process.index')->with('success', 'تم حذف العملية بنجاح');
    }
}
