<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\{Client, Item};
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\{DB, Auth};
use Modules\CRM\Http\Requests\StoreReturnRequest;
use Modules\CRM\Models\{ReturnItem, ReturnOrder};

class ReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Returns')->only(['index', 'show']);
        $this->middleware('can:create Returns')->only(['create', 'store']);
        $this->middleware('can:edit Returns')->only(['edit', 'update', 'approve', 'reject']);
        $this->middleware('can:delete Returns')->only(['destroy']);
    }

    public function index()
    {
        $returns = ReturnOrder::with(['client', 'createdBy', 'approvedBy', 'items'])
            ->latest()
            ->get();

        return view('crm::returns.index', compact('returns'));
    }

    public function create()
    {
        $branches = userBranches();
        $clients = Client::all();
        $items = Item::all();

        return view('crm::returns.create', compact('clients', 'items', 'branches'));
    }

    public function store(StoreReturnRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $itemsData = $validated['items'];
            unset($validated['items']);

            $validated['created_by'] = Auth::id();
            $validated['status'] = 'pending';

            $returnOrder = ReturnOrder::create($validated);

            foreach ($itemsData as $item) {
                ReturnItem::create([
                    'return_id'      => $returnOrder->id,
                    'item_id'        => $item['item_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'item_condition' => $item['item_condition'] ?? null,
                    'notes'          => $item['notes'] ?? null,
                ]);
            }

            $returnOrder->calculateTotal();
            $returnOrder->update(['refund_amount' => $returnOrder->total_amount]);

            DB::commit();

            return redirect()->route('returns.index')->with('message', __('Return created successfully'));
        } catch (\Exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => __('Error: ')]);
        }
    }


    public function show(ReturnOrder $return)
    {
        $return->load(['client', 'createdBy', 'approvedBy', 'items.item']);
        return view('crm::returns.show', compact('return'));
    }

    public function edit(ReturnOrder $return)
    {
        if ($return->status != 'pending') {
            return redirect()->back()->with('error', __('Cannot edit non-pending returns'));
        }

        $clients = Client::all();
        $items = Item::all();
        $return->load('items.item');

        return view('crm::returns.edit', compact('return', 'clients', 'items'));
    }

    public function update(StoreReturnRequest $request, ReturnOrder $return)
    {
        $validated = $request->validated();

        if ($return->status != 'pending') {
            return redirect()->back()->with('error', __('Cannot edit processed returns'));
        }

        DB::beginTransaction();
        try {
            $itemsData = $validated['items'];
            unset($validated['items']);

            $return->update([
                'client_id'               => $validated['client_id'],
                'return_date'             => $validated['return_date'],
                'return_type'             => $validated['return_type'],
                'original_invoice_number' => $validated['original_invoice_number'],
                'original_invoice_date'   => $validated['original_invoice_date'],
                'branch_id'               => $validated['branch_id'],
                'reason'                  => $validated['reason'],
                'notes'                   => $validated['notes'],
            ]);

            $return->items()->delete();

            foreach ($itemsData as $item) {
                ReturnItem::create([
                    'return_id'      => $return->id,
                    'item_id'        => $item['item_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'item_condition' => $item['item_condition'] ?? null,
                    'notes'          => $item['notes'] ?? null,
                ]);
            }

            $return->calculateTotal();
            $return->update(['refund_amount' => $return->total_amount]);

            DB::commit();

            return redirect()->route('returns.show', $return->id)->with('message', __('Return updated successfully'));
        } catch (\Exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => __('Error: ')]);
        }
    }

    public function approve(ReturnOrder $return)
    {
        $return->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('returns.show', $return)->with('message', __('Return approved successfully'));
    }

    public function reject(ReturnOrder $return)
    {
        $return->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('returns.show', $return)->with('message', __('Return rejected successfully'));
    }

    public function destroy(ReturnOrder $return)
    {
        if ($return->status != 'pending') {
            return redirect()->back()->with('error', __('Cannot delete processed returns'));
        }

        $return->delete();
        return redirect()->route('returns.index')->with('message', __('Return deleted successfully'));
    }
}
