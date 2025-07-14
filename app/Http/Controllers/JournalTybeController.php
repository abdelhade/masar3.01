<?php

namespace App\Http\Controllers;

use App\Models\JournalTybe;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JournalTybeController extends Controller
{
    // عرض جميع السجلات
    public function index()
    {
        $journalTybes = JournalTybe::all();
        return view('journal_tybes.index', compact('journalTybes'));
    }

    // عرض نموذج إضافة سجل جديد
    public function create()
    {
        return view('journal_tybes.create');
    }

    // إضافة سجل جديد
    public function store(Request $request)
    {
        $request->validate([
            'journal_id' => 'required|integer',
            'jname' => 'required|string|max:222',
            'jtext' => 'nullable|string|max:222',
            'info' => 'nullable|string|max:222',
            'isdeleted' => 'nullable|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
        ]);

        JournalTybe::create($request->all());

        return redirect()->route('journal_tybes.index')->with('success', 'تم إضافة السجل بنجاح');
    }

    // عرض تفاصيل سجل معين
    public function show($id)
    {
        $journalTybe = JournalTybe::findOrFail($id);
        return view('journal_tybes.show', compact('journalTybe'));
    }

    // عرض نموذج تعديل سجل
    public function edit($id)
    {
        $journalTybe = JournalTybe::findOrFail($id);
        return view('journal_tybes.edit', compact('journalTybe'));
    }

    // تحديث السجل
    public function update(Request $request, $id)
    {
        $request->validate([
            'journal_id' => 'required|integer',
            'jname' => 'required|string|max:222',
            'jtext' => 'nullable|string|max:222',
            'info' => 'nullable|string|max:222',
            'isdeleted' => 'nullable|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
        ]);

        $journalTybe = JournalTybe::findOrFail($id);
        $journalTybe->update($request->all());

        return redirect()->route('journal_tybes.index')->with('success', 'تم تعديل السجل بنجاح');
    }

    // حذف السجل
    public function destroy($id)
    {
        $journalTybe = JournalTybe::findOrFail($id);
        $journalTybe->delete();

        return redirect()->route('journal_tybes.index')->with('success', 'تم حذف السجل بنجاح');
    }
}
