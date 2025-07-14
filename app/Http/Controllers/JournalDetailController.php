<?php

namespace App\Http\Controllers;

use App\Models\Journaldetail;
use App\Models\JournalHead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JournalDetailController extends Controller
{
    public function index()
    {
        $entries = Journaldetail::with('journalHead', 'accountHead')->get();
        return view('journal_entries.index', compact('entries'));
    }

    public function create()
    {
        $journalHeads = JournalHead::all();
        return view('journal_entries.create', compact('journalHeads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'journal_id' => 'required|exists:journal_heads,id',
            'account_id' => 'required|exists:acc_head,id',
            'debit'      => 'required|integer',
            'credit'     => 'required|integer',
            'tybe'       => 'required|integer',
        ]);

        JournalDetail::create($request->all());
        return redirect()->route('journal-entries.index');
    }

    public function show($id)
    {
        $entry = JournalDetail::findOrFail($id);
        return view('journal_entries.show', compact('entry'));
    }

    public function edit($id)
    {
        $entry = JournalDetail::findOrFail($id);
        $journalHeads = JournalHead::all();
        return view('journal_entries.edit', compact('entry', 'journalHeads'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'journal_id' => 'required|exists:journal_heads,id',
            'account_id' => 'required|exists:acc_head,id',
            'debit' => 'required|integer',
            'credit' => 'required|integer',
            'tybe' => 'required|integer',
        ]);

        $entry = JournalDetail::findOrFail($id);
        $entry->update($request->all());

        return redirect()->route('journal-entries.index');
    }

    public function destroy($id)
    {
        $entry = JournalDetail::findOrFail($id);
        $entry->delete();

        return redirect()->route('journal-entries.index');
    }
}
