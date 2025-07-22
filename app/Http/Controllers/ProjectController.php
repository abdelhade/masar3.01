<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use App\Models\OperHead;
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
    public function show($id)
    {
        $project = Project::findOrFail($id);

        $operations = OperHead::where('project_id', $id)->get();
        $equipments = AccHead::where('rent_to', $id)->get();
        
        // للحصول على عمليات المعدات لكل معدة
        $equipmentOperations = collect();
        foreach ($equipments as $equipment) {
            $operation = OperHead::where('acc3', $equipment->id)->orderBy('start_date')->first();
            if ($operation) {
                $equipmentOperations->push([
                    'equipment' => $equipment,
                    'operation' => $operation
                ]);
            }
        }
        
        $vouchers = OperHead::where('project_id', $id)
            ->where(function($query) {
                $query->where('pro_type', 1)
                      ->orWhere('pro_type', 2);
            })
            ->get();

        return view('projects.show', compact('project', 'operations', 'equipments', 'vouchers', 'equipmentOperations'));
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
