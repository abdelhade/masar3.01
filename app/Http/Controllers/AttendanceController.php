<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض البصمات')->only(['index']);
        $this->middleware('can:إضافة البصمات')->only(['create', 'store']);
        $this->middleware('can:تعديل البصمات')->only(['edit', 'update']);
        $this->middleware('can:حذف البصمات')->only(['destroy']);
    }

    public function index()
    {
        return view('hr-management.attendances.attendance.index');
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
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
