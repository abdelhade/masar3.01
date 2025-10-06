<?php

namespace App\Http\Controllers;

use App\Models\Varibal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VaribalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('item-management.varibals.manage-varibals');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('item-management.varibals.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Varibal::create($request->all());

        return redirect()->route('varibals.index')
            ->with('success', 'تم إنشاء المتغير بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Varibal $varibal)
    {
        return view('item-management.varibals.show', compact('varibal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Varibal $varibal)
    {
        return view('item-management.varibals.edit', compact('varibal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Varibal $varibal)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $varibal->update($request->all());

        return redirect()->route('varibals.index')
            ->with('success', 'تم تحديث المتغير بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Varibal $varibal)
    {
        $varibal->delete();

        return redirect()->route('varibals.index')
            ->with('success', 'تم حذف المتغير بنجاح');
    }

    /**
     * Get varibals for API
     */
    public function api()
    {
        $varibals = Varibal::select('id', 'name', 'description')
            ->orderBy('name')
            ->get();

        return response()->json($varibals);
    }
}
