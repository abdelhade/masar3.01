<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:عرض المشاريع')->only(['index']);
        $this->middleware('can:إضافة المشاريع')->only(['create', 'store']);
        $this->middleware('can:تعديل المشاريع')->only(['update', 'edit']);
        $this->middleware('can:حذف العملاء')->only(['destroy']); // ده خاص بالعملاء، هل تقصدي المشاريع؟
    }

    public function index()
    {
        return view('projects.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
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
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
