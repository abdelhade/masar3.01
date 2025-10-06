<?php

namespace App\Http\Controllers;

use App\Models\VaribalValue;
use Illuminate\Http\Request;
use App\Models\Varibal;

class VaribalValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($varibalId = null)
    {
        $varibal = Varibal::findOrFail($varibalId);
        return view('item-management.varibals.manage-varibal-values', compact('varibal', 'varibalId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VaribalValue $varibalValue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VaribalValue $varibalValue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VaribalValue $varibalValue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VaribalValue $varibalValue)
    {
        //
    }
}
