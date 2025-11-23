<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CvController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view CVs')->only(['index']);
        $this->middleware('can:create CVs')->only(['create', 'store']);
        $this->middleware('can:edit CVs')->only(['edit', 'update']);
        $this->middleware('can:delete CVs')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hr-management.cvs.manage-cvs');
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
    public function show(Cv $cv)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cv $cv)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cv $cv)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cv $cv)
    {
        //
    }
}
