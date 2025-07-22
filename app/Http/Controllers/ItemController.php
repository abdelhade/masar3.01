<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض الأصناف')->only(['index', 'show']);
        $this->middleware('can:إضافة الأصناف')->only(['create', 'store']);
        $this->middleware('can:تعديل الأصناف')->only(['edit', 'update']);
        $this->middleware('can:حذف الأصناف')->only(['destroy']);
    }
    public function index()
    {
        return view('item-management.items.index');
    }

    public function create()
    {
        return view('item-management.items.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $itemModel = Item::findOrFail($id);
        return view('item-management.items.edit', compact('itemModel'));
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    // 📁 Item Movement
    public function itemMovementReport($itemId = null, $warehouseId = null)
    {
        return view('item-management.reports.item-movement', compact('itemId', 'warehouseId')); // itemId and warehouseId are optional
    }

    // Get item as JSON for AJAX requests
    public function getItemJson($id)
    {
        $item = Item::with(['units', 'prices'])->findOrFail($id);
        return response()->json($item);
    }
}
