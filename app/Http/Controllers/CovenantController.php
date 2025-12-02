<?php

namespace App\Http\Controllers;

use App\Models\Covenant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class CovenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Covenants')->only(['index']);
        $this->middleware('can:create Covenants')->only(['create', 'store']);
        $this->middleware('can:edit Covenants')->only(['edit', 'update']);
        $this->middleware('can:delete Covenants')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hr-management.covenants.manage-covenant');
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
    public function show(Covenant $covenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Covenant $covenant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Covenant $covenant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Covenant $covenant)
    {
        //
    }
}
