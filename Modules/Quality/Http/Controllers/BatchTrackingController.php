<?php

namespace Modules\Quality\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quality\Models\BatchTracking;
use App\Models\Item;
use Modules\Accounts\Models\AccHead;

class BatchTrackingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view batches')->only(['index' , 'show']);
        $this->middleware('can:create batches')->only(['create', 'store']);
        $this->middleware('can:edit batches')->only(['edit', 'update']);
        $this->middleware('can:delete batches')->only(['destroy']);
    }
    public function index()
    {
        $batches = BatchTracking::with(['item', 'supplier', 'warehouse'])
            ->orderBy('production_date', 'desc')
            ->paginate(20);

        $stats = [
            'total' => BatchTracking::count(),
            'active' => BatchTracking::where('status', 'active')->count(),
            'expiring_soon' => BatchTracking::expiringSoon(30)->count(),
            'expired' => BatchTracking::expired()->count(),
        ];

        return view('quality::batches.index', compact('batches', 'stats'));
    }

    public function create()
    {
        $items = Item::where('isdeleted', 0)->get();
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();
        $warehouses = AccHead::where('code', 'like', '13%')->where('isdeleted', 0)->get();

        return view('quality::batches.create', compact('items', 'suppliers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|unique:batch_tracking,batch_number',
            'item_id' => 'required|exists:items,id',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:production_date',
            'quantity' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:acc_head,id',
            'warehouse_id' => 'nullable|exists:acc_head,id',
            'location' => 'nullable|string',
            'quality_status' => 'required|in:passed,failed,conditional,quarantine',
            'notes' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branches()->where('is_active', 1)->first()->id ?? 1;
        $validated['remaining_quantity'] = $validated['quantity'];
        $validated['status'] = 'active';
        $validated['created_by'] = auth()->id();

        $batch = BatchTracking::create($validated);

        return redirect()->route('quality.batches.show', $batch)
            ->with('success', 'تم إنشاء الدفعة بنجاح');
    }

    public function show(BatchTracking $batch)
    {
        $batch->load(['item', 'supplier', 'warehouse', 'inspection']);

        return view('quality::batches.show', compact('batch'));
    }

    public function edit(BatchTracking $batch)
    {
        $items = Item::where('isdeleted', 0)->get();
        $suppliers = AccHead::where('code', 'like', '2101%')->where('isdeleted', 0)->get();
        $warehouses = AccHead::where('code', 'like', '13%')->where('isdeleted', 0)->get();

        return view('quality::batches.edit', compact('batch', 'items', 'suppliers', 'warehouses'));
    }

    public function update(Request $request, BatchTracking $batch)
    {
        $validated = $request->validate([
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:production_date',
            'remaining_quantity' => 'required|numeric|min:0|max:' . $batch->quantity,
            'warehouse_id' => 'nullable|exists:acc_head,id',
            'location' => 'nullable|string',
            'quality_status' => 'required',
            'status' => 'required',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $batch->update($validated);

        return redirect()->route('quality.batches.show', $batch)
            ->with('success', 'تم تحديث الدفعة بنجاح');
    }

    public function destroy(BatchTracking $batch)
    {
        $batch->delete();

        return redirect()->route('quality.batches.index')
            ->with('success', 'تم حذف الدفعة بنجاح');
    }
}

