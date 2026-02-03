<?php

namespace Modules\HR\Http\Controllers;

use Modules\HR\Models\AttendanceProcessingDetail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AttendanceProcessingDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Attendance');
    }

    /**
     * Display a listing of the resource.
     */
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
    public function show(AttendanceProcessingDetail $attendanceProcessingDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceProcessingDetail $attendanceProcessingDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttendanceProcessingDetail $attendanceProcessingDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceProcessingDetail $attendanceProcessingDetail)
    {
        //
    }
}
