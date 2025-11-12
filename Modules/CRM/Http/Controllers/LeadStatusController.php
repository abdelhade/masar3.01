<?php

namespace Modules\CRM\Http\Controllers;

use Modules\CRM\Models\LeadStatus;
use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\LeadStatusRequest;

class LeadStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Lead Statuses')->only(['index']);
        $this->middleware('can:create Lead Statuses')->only(['create', 'store']);
        $this->middleware('can:edit Lead Statuses')->only(['edit', 'update']);
        $this->middleware('can:delete Lead Statuses')->only(['destroy']);
    }

    public function index()
    {
        $leadStatus = LeadStatus::all();
        return view('crm::lead-status.index', compact('leadStatus'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('crm::lead-status.create', compact('branches'));
    }

    public function store(LeadStatusRequest $request)
    {
        LeadStatus::create($request->validated());
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('lead-status.index');
    }

    public function show($id)
    {
        // return view('crm::show');
    }

    public function edit($id)
    {
        $leadStatus = LeadStatus::findOrFail($id);
        return view('crm::lead-status.edit', compact('leadStatus'));
    }

    public function update(LeadStatusRequest $request, $id)
    {
        $leadStatus = LeadStatus::findOrFail($id);
        $leadStatus->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('lead-status.index');
    }

    public function destroy($id)
    {
        $leadStatus = LeadStatus::findOrFail($id);
        $leadStatus->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('lead-status.index');
    }
}
