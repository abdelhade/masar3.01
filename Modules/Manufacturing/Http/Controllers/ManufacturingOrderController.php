<?php

namespace Modules\Manufacturing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ManufacturingOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Manufacturing Orders')->only(['index', 'show']);
        $this->middleware('permission:create Manufacturing Orders')->only(['create', 'store']);
        $this->middleware('permission:edit Manufacturing Orders')->only(['edit', 'update']);
        $this->middleware('permission:delete Manufacturing Orders')->only('destroy');
    }

    public function index()
    {
        return view('manufacturing::manufacturing-order.index');
    }

    public function create()
    {
        return view('manufacturing::manufacturing-order.create');
    }

    public function store(Request $request) {}

    public function show($id)
    {
        return view('manufacturing::show');
    }

    public function edit($id)
    {
        return view('manufacturing::edit');
    }

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
