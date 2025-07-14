<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;



class KpiController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض المعدلات')->only(['index']);
        $this->middleware('can:إنشاء المعدلات')->only(['create', 'store']);
        $this->middleware('can:تعديل المعدلات')->only(['update', 'edit']);
        $this->middleware('can:حذف المعدلات')->only(['destroy']);
    }

    public function index()
    {
        return view('hr-management.kpis.manage-kpi');
    }

    public function employeeEvaluation()
    {
        return view('hr-management.kpis.manage-employee-evaluation');
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
    public function show(Kpi $kpi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kpi $kpi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kpi $kpi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kpi $kpi)
    {
        //
    }
}
