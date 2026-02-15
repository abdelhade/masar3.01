<?php

namespace Modules\Branches\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Branches\Http\Requests\BranchesRequest;
use Modules\Branches\Models\Branch;
use RealRashid\SweetAlert\Facades\Alert;

class BranchesController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Branches')->only(['index']);
        $this->middleware('can:create Branches')->only(['create', 'store']);
        $this->middleware('can:edit Branches')->only(['edit', 'update']);
        $this->middleware('can:delete Branches')->only(['destroy']);
    }

    public function index()
    {
        $branches = Branch::all();

        return view('branches::branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches::branches.create');
    }

    public function store(BranchesRequest $request)
    {
        try {
            Branch::create($request->validated());
            Alert::toast(__('Branch created successfully'), 'success');

            return redirect()->route('branches.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);

        return view('branches::branches.edit', compact('branch'));
    }

    public function update(BranchesRequest $request, $id)
    {
        try {
            $branch = Branch::findOrFail($id);
            $branch->update($request->validated());
            Alert::toast(__('Branch updated successfully'), 'success');

            return redirect()->route('branches.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {

            $branch = Branch::findOrFail($id);
            $branch->delete();
            Alert::toast(__('Branch deleted successfully'), 'success');

            return redirect()->route('branches.index');
        } catch (\Exception) {
            Alert::toast(__('An error occurred'), 'error');

            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);

        return view('branches::branches.show', compact('branch'));
    }

    public function toggleStatus(Request $request)
    {
        $branch = Branch::findOrFail($request->id);
        $branch->is_active = $request->is_active;
        $branch->save();

        return response()->json(['success' => true]);
    }
}
