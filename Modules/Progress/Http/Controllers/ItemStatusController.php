<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\ItemStatus;
use Illuminate\Http\Request;

class ItemStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view progress-item-statuses')->only('index');
        $this->middleware('can:create progress-item-statuses')->only(['create', 'store']);
        $this->middleware('can:edit progress-item-statuses')->only(['edit', 'update']);
        $this->middleware('can:delete progress-item-statuses')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statuses = ItemStatus::ordered()->get();
        return view('progress::item_statuses.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('progress::item_statuses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_statuses,name',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        ItemStatus::create($validated);

        return redirect()
            ->route('progress.item-statuses.index')
            ->with('success', __('general.item_status_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemStatus $itemStatus)
    {
        return view('progress::item_statuses.show', compact('itemStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemStatus $itemStatus)
    {
        return view('progress::item_statuses.edit', compact('itemStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemStatus $itemStatus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_statuses,name,' . $itemStatus->id,
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $itemStatus->update($validated);

        return redirect()
            ->route('progress.item-statuses.index')
            ->with('success', __('general.item_status_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemStatus $itemStatus)
    {
        // Check if status is used in any project items
        if ($itemStatus->projectItems()->count() > 0) {
            return back()
                ->with('error', __('general.cannot_delete_item_status_in_use'));
        }

        $itemStatus->delete();

        return redirect()
            ->route('progress.item-statuses.index')
            ->with('success', __('general.item_status_deleted_successfully'));
    }
}

