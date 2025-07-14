<?php

namespace App\Http\Controllers;

use App\Models\EmployeesJob;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmployeesJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض الوظائف')->only(['index']);
        $this->middleware('can:إضافة الوظائف')->only(['create', 'store']);
        $this->middleware('can:تعديل الوظائف')->only(['edit', 'update']);
        $this->middleware('can:حذف الوظائف')->only(['destroy']);
        $this->middleware('can:طباعة الوظائف')->only(['print']); // لو في طباعة فعلًا
    }

    public function index()
    {
        return view('hr-management.jobs.manage-jobs');
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
    public function show(EmployeesJob $employeesJob)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeesJob $employeesJob)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeesJob $employeesJob)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeesJob $employeesJob)
    {
        //
    }
}
