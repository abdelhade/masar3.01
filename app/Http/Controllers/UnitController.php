<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض المجموعات')->only(['index']);
    }

    public function index()
    {
        $units = Unit::all();
        return view('item-management.units.manage-units', compact('units'));
    }

    public function create()
    {
        //
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
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
