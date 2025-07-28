<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Magical;

class MagicalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $magicals = Magical::all();
        return view("magicals.index", compact("magicals"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("magicals.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            'magic_name' => 'required|string|max:255',
            'magic_link' => 'required|string|max:255|unique:magicals,magic_link',
            'info' => 'string|max:255',
            'is_journal' => 'nullable',
            'type' => 'required|array',
            'option' => 'array',
            'name' => 'required|array',
            'value' => 'required|array',
            'placeholder' => 'required|array',
        ]);

        $magical = \App\Models\Magical::create([
            'magic_name' => $request->magic_name,
            'magic_link' => $request->magic_link,
            'info' => $request->info,
            'is_journal' => $request->is_journal ? 1 : 0,
        ]);

        foreach ($request->type as $i => $type) {
            \App\Models\MagicaDet::create([
                'magical_id' => $magical->id,
                'type' => $type,
                'option' => $request->option[$i] ?? '',
                'name' => $request->name[$i] ?? '',
                'value' => $request->value[$i] ?? '',
                'placeholder' => $request->placeholder[$i] ?? '',
                'class' => '', // يمكنك تعديلها لاحقًا حسب الحاجة
            ]);
        }
        try {
            // All logic above already executed
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
        return redirect()->route('magicals.index')->with('success', 'Magical created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view("magicals.show", compact("magical"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view("magicals.edit", compact("magical"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'magic_name' => 'required|string|max:255',
            'magic_link' => 'required|string|max:255',
            'is_journal' => 'required|boolean',
        ]);
        Magical::find($id)->update($request->all());
        return redirect()->route('magicals.index')->with('success', 'Magical updated successfully');
    }

    /**
     * Remove the specified resource from storage.  
     */
    public function destroy(string $id)
    {
        Magical::find($id)->delete();
        return redirect()->route('magicals.index')->with('success', 'Magical deleted successfully');
    }
}
