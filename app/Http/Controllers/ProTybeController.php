<?php

namespace App\Http\Controllers;

use App\Models\ProType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProTybeController extends Controller
{
    public function index()
    {
        $types = ProType::where('isdeleted', 0)->get();
        return view('pro_tybes.index', compact('types'));
    }

    public function create()
    {
        return view('pro_tybes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pname' => 'required|string|max:200',
            'ptybe' => 'nullable|integer',
        ]);

        ProType::create($request->all());

        return redirect()->route('pro_tybes.index')->with('success', 'تم الحفظ بنجاح');
    }

    public function edit(ProType $proTybe)
    {
        return view('pro_types.edit', compact('proTybe'));
    }

    public function update(Request $request, ProType $proTybe)
    {
        $request->validate([
            'pname' => 'required|string|max:200',
            'ptybe' => 'nullable|integer',
        ]);

        $proTybe->update($request->all());

        return redirect()->route('pro_tybes.index')->with('success', 'تم التعديل بنجاح');
    }

    public function destroy(ProType $proTybe)
    {
        $proTybe->update(['isdeleted' => 1]);

        return redirect()->route('pro_tybes.index')->with('success', 'تم الحذف بنجاح');
    }
}
