<?php

namespace App\Http\Controllers;

use App\Models\Employee_Evaluation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class EmployeeEvaluationController extends Controller
{
    // public function __construct()
    // {

    //     $this->middleware('can:عرض معدلات')->only(['index']);
    //     $this->middleware('can:إنشاء المعدلات')->only(['create', 'store']);
    //     $this->middleware('can:تعديل المعدلات')->only(['update', 'edit']);
    //     $this->middleware('can:حذف المناطق')->only(['destroy']);
    // }
    public function index()
    {
        //
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
    public function show(Employee_Evaluation $employee_Evaluation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee_Evaluation $employee_Evaluation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee_Evaluation $employee_Evaluation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee_Evaluation $employee_Evaluation)
    {
        //
    }
}
