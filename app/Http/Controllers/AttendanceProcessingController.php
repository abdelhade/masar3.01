<?php

namespace App\Http\Controllers;

use App\Models\AttendanceProcessing;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class AttendanceProcessingController extends Controller
{
    public function __construct()
    {

        $this->middleware('can:عرض معالجه الحضور والانصراف')->only(['index']);
        $this->middleware('can:إنشاء معالجة الحضور والانصراف')->only(['create', 'store']);
        $this->middleware('can:تعديل معالجة الحضور والانصراف')->only(['update', 'edit']);
        $this->middleware('can:حذف معالجة الحضور والانصراف')->only(['destroy']);
    }
    public function index()
    {
        return view('hr-management.attendances.processing.manage-processing');
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
    public function show(AttendanceProcessing $AttendanceProcessing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceProcessing $AttendanceProcessing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttendanceProcessing $AttendanceProcessing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceProcessing $AttendanceProcessing)
    {
        //
    }
}
