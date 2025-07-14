<?php

namespace App\Http\Controllers;

use App\Models\Operhead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OperHeadController  extends Controller
{
    public function index()
    {
        // $operheads = Operhead::all();
        $operheads = Operhead::with('type')->get();
        dd($operheads);
    }

    public function store(Request $request)
    {
        $operhead = Operhead::create($request->all());
        return response()->json($operhead, 201);
    }

    public function show($id)
    {
        $operhead = Operhead::findOrFail($id);
        return response()->json($operhead);
    }

    public function update(Request $request, $id)
    {
        $operhead = Operhead::findOrFail($id);
        $operhead->update($request->all());
        return response()->json($operhead);
    }

    public function destroy($id)
    {
        $operhead = Operhead::findOrFail($id);
        $operhead->delete();
        return response()->json(null, 204);
    }
}
