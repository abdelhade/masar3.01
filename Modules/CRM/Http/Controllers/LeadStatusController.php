<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Http\Requests\LeadStatusRequest;
use Modules\CRM\Models\LeadStatus;
use RealRashid\SweetAlert\Facades\Alert;

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
        $leadStatus = LeadStatus::orderBy('order_column')->paginate(20);
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
        Alert::toast(__('Lead status created successfully'), 'success');
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

    public function update(LeadStatusRequest $request, LeadStatus $leadStatus)
    {
        $leadStatus->update($request->validated());
        Alert::toast(__('Lead status updated successfully'), 'success');
        return redirect()->route('lead-status.index');
    }

    public function destroy(LeadStatus $leadStatus)
    {
        try {
            $leadStatus->delete();
            Alert::toast(__('Lead status deleted successfully'), 'success');
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
            Alert::toast(__('An error occurred while deleting the lead status'), 'error');
        }
        return redirect()->route('lead-status.index');
    }
}
