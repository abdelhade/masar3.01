<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;


use Illuminate\Http\Request;

class DepartmentController extends Controller
{
   public function __construct()
{
    $this->middleware('can:عرض الأقسام')->only(['index']);
    $this->middleware('can:إضافة الأقسام')->only(['create', 'store']);
    $this->middleware('can:تعديل الأقسام')->only(['update', 'edit']);
    $this->middleware('can:حذف الأقسام')->only(['destroy']);
}

    public function index()
    {
        return view('hr-management.departments.manage-department');
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
