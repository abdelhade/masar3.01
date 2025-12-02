<?php

namespace App\Http\Controllers;

use App\Models\WorkPermission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class WorkPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Work Permissions')->only(['index']);
        $this->middleware('can:create Work Permissions')->only(['create', 'store']);
        $this->middleware('can:edit Work Permissions')->only(['edit', 'update']);
        $this->middleware('can:delete Work Permissions')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hr-management.work-permissions.manage-work-permissions');
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
    public function show(WorkPermission $workPermission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkPermission $workPermission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkPermission $workPermission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkPermission $workPermission)
    {
        //
    }
}
