<?php

namespace App\Http\Controllers;

use App\Models\Errand;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class ErrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Errands')->only(['index']);
        $this->middleware('can:create Errands')->only(['create', 'store']);
        $this->middleware('can:edit Errands')->only(['edit', 'update']);
        $this->middleware('can:delete Errands')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hr-management.errands.manage-errands');
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
    public function show(Errand $errand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Errand $errand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Errand $errand)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Errand $errand)
    {
        //
    }
}
