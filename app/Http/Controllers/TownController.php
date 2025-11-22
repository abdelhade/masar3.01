<?php

namespace App\Http\Controllers;

use App\Models\Town;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TownController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Towns')->only(['index']);
        $this->middleware('can:create Towns')->only(['create', 'store']);
        $this->middleware('can:edit Towns')->only(['update', 'edit']);
        $this->middleware('can:delete Towns')->only(['destroy']);
    }
    public function index()
    {
        return view('hr-management.addresses.manage-towns');
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
    public function show(Town $town)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Town $town)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Town $town)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Town $town)
    {
        //
    }
}
