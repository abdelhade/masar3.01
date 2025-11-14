<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Http\Requests\ChanceSourceRequest;
use Modules\CRM\Models\ChanceSource;
use RealRashid\SweetAlert\Facades\Alert;

class ChanceSourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Chance Sources')->only(['index']);
        $this->middleware('permission:create Chance Sources')->only(['create', 'store']);
        $this->middleware('permission:edit Chance Sources')->only(['edit', 'update']);
        $this->middleware('permission:delete Chance Sources')->only(['destroy']);
    }

    public function index()
    {
        $chanceSources = ChanceSource::all();
        return view('crm::chance-source.index', compact('chanceSources'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('crm::chance-source.create', compact('branches'));
    }

    public function store(ChanceSourceRequest $request)
    {
        ChanceSource::create($request->validated());
        Alert::toast(__('Item created successfully'), 'success');
        return redirect()->route('chance-sources.index');
    }

    public function show($id)
    {
        // return view('crm::show');
    }
    public function edit($id)
    {
        $chanceSource = ChanceSource::findOrFail($id);
        return view('crm::chance-source.edit', compact('chanceSource'));
    }

    public function update(ChanceSourceRequest $request, ChanceSource $chanceSource)
    {
        $chanceSource->update($request->validated());
        Alert::toast(__('Item updated successfully'), 'success');
        return redirect()->route('chance-sources.index');
    }

    public function destroy(ChanceSource $chanceSource)
    {
        try {
            $chanceSource->delete();
            Alert::toast(__('Item deleted successfully'), 'success');
        } catch (\Exception ) {
            Alert::toast(__('An error occurred while deleting the item'), 'error');
        }
        return redirect()->route('chance-sources.index');
    }
}
