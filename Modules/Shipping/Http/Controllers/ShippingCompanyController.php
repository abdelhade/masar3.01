<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Http\Requests\ShippingCompanyRequest;
use Modules\Shipping\Models\ShippingCompany;
use RealRashid\SweetAlert\Facades\Alert;

class ShippingCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Shipping Companies')->only(['index']);
        $this->middleware('permission:create Shipping Companies')->only(['create', 'store']);
        $this->middleware('permission:edit Shipping Companies')->only(['edit', 'update']);
        $this->middleware('permission:delete Shipping Companies')->only(['destroy']);
    }

    public function index()
    {
        $companies = ShippingCompany::paginate(10);

        return view('shipping::companies.index', compact('companies'));
    }

    public function create()
    {
        $branches = userBranches();

        return view('shipping::companies.create', compact('branches'));
    }

    public function store(ShippingCompanyRequest $request)
    {
        ShippingCompany::create($request->validated());
        Alert::toast(__('Shipping Company created successfully.'), 'success');

        return redirect()->route('companies.index');
    }

    public function edit(ShippingCompany $company)
    {
        return view('shipping::companies.edit', compact('company'));
    }

    public function update(ShippingCompanyRequest $request, ShippingCompany $company)
    {
        $company->update($request->validated());
        Alert::toast(__('Shipping Company updated successfully.'), 'success');

        return redirect()->route('companies.index');
    }

    public function show(ShippingCompany $company)
    {
        $company->load('branch');

        return view('shipping::companies.show', compact('company'));
    }

    public function destroy(ShippingCompany $company)
    {
        if ($company->shipments()->exists()) {
            Alert::toast(__('Cannot delete company with existing shipments.'), 'error');

            return redirect()->route('companies.index');
        }

        $company->delete();
        Alert::toast(__('Shipping Company deleted successfully.'), 'success');

        return redirect()->route('companies.index');
    }
}
