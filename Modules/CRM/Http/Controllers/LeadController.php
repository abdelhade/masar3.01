<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;
use Illuminate\Routing\Controller;
use Modules\CRM\Models\LeadStatus;


class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Leads')->only(['board']);
        $this->middleware('can:create Leads')->only(['store']);
        $this->middleware('can:edit Leads')->only(['updateStatus']);
        $this->middleware('can:delete Leads')->only(['destroy']);
    }

    public function board()
    {
        $statuses = LeadStatus::orderBy('name')->get();
        $leads = Lead::with(['client', 'status', 'assignedTo'])
            ->get()
            ->groupBy('status_id');

        $clients = Client::all();
        $users = User::all();


        return view('crm::leads.board', compact('statuses', 'leads', 'clients', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'status_id' => 'required|exists:crm_lead_statuses,id',
            'amount' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string'
        ]);

        Lead::create($validated);

        return redirect()->route('leads.board')->with('message', __('lead_added_successfully'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:crm_leads,id',
            'new_status_id' => 'required|exists:crm_lead_statuses,id'
        ]);

        $lead = Lead::find($request->lead_id);
        $lead->update(['status_id' => $request->new_status_id]);

        return response()->json([
            'success' => true,
            'message' => __('lead_status_updated'),
            'new_status' => $lead->status->name
        ]);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.board')->with('message', __('lead_deleted_successfully'));
    }
}
