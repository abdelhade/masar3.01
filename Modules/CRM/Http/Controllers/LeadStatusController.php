<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CRM\Models\LeadStatus;
use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\LeadStatusRequest;

class LeadStatusController extends Controller
{
    public function __construct()
    {
        // $this->middleware('can:view lead-status')->only(['index']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leadStatus = LeadStatus::all();
        return view('crm::lead-status.index', compact('leadStatus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('crm::lead-status.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LeadStatusRequest $request)
    {
        LeadStatus::create($request->validated());
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('lead-status.index');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $leadStatus = LeadStatus::findOrFail($id);
        return view('crm::lead-status.edit', compact('leadStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LeadStatusRequest $request, $id)
    {
        $leadStatus = LeadStatus::findOrFail($id);
        $leadStatus->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('lead-status.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $leadStatus = LeadStatus::findOrFail($id);
        $leadStatus->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('lead-status.index');
    }
}
