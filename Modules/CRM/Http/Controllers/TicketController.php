<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Modules\CRM\Models\Ticket;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Models\TicketComment;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Tickets')->only(['index', 'show']);
        $this->middleware('can:create Tickets')->only(['create', 'store']);
        $this->middleware('can:edit Tickets')->only(['edit', 'update', 'updateStatus', 'addComment']);
        $this->middleware('can:delete Tickets')->only(['destroy']);
    }

    public function index()
    {
        $tickets = Ticket::with(['client', 'assignedTo', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        $clients = Client::all();
        $users = User::all();

        return view('crm::tickets.index', compact('tickets', 'clients', 'users'));
    }

    public function create()
    {
        $branches = userBranches();
        $clients = Client::all();
        $users = User::all();

        return view('crm::tickets.create', compact('clients', 'users', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] =Auth::id();
        $validated['status'] = 'open';

        Ticket::create($validated);

        return redirect()->route('tickets.index')->with('message', __('Ticket created successfully'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['client', 'assignedTo', 'createdBy', 'comments.user']);
        $users = User::all();

        return view('crm::tickets.show', compact('ticket', 'users'));
    }

    public function edit(Ticket $ticket)
    {
        $branches = userBranches();
        $clients = Client::all();
        $users = User::all();

        return view('crm::tickets.edit', compact('ticket', 'clients', 'users', 'branches'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket)->with('message', __('Ticket updated successfully'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:crm_tickets,id',
            'new_status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket = Ticket::find($request->ticket_id);
        $ticket->updateStatus($request->new_status);

        $ticket->updateStatus($request->new_status);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Ticket status updated successfully')
            ]);
        }

        return redirect()->back()->with('message', __('Ticket status updated successfully'));
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('tickets.show', $ticket)->with('message', __('Comment added successfully'));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('message', __('Ticket deleted successfully'));
    }
}
