<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Shifts')->only(['index']);
        $this->middleware('can:create Shifts')->only(['create', 'store']);
        $this->middleware('can:edit Shifts')->only(['update', 'edit']);
        $this->middleware('can:delete Shifts')->only(['destroy']);
    }

    public function index()
    {
        return view('hr-management.shifts.manage-shifts');
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
