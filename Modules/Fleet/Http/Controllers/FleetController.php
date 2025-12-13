<?php

namespace Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('fleet::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fleet::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('fleet::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('fleet::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
