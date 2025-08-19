<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shipping\Models\ShippingCompany;
use Modules\Shipping\Http\Requests\ShippingCompanyRequest;
use RealRashid\SweetAlert\Facades\Alert;

class ShippingCompanyController extends Controller
{
    public function index()
    {
        $companies = ShippingCompany::paginate(10);
        return view('shipping::companies.index', compact('companies'));
    }

    public function create()
    {
        return view('shipping::companies.create');
    }

    public function store(ShippingCompanyRequest $request)
    {
        ShippingCompany::create($request->validated());
        Alert::toast('تم إنشاء الشركة بنجاح.', 'success');
        return redirect()->route('companies.index');
    }

    public function edit(ShippingCompany $company)
    {
        return view('shipping::companies.edit', compact('company'));
    }

    public function update(ShippingCompanyRequest $request, ShippingCompany $company)
    {
        $company->update($request->validated());
        Alert::toast('تم تحديث الشركة بنجاح.', 'success');
        return redirect()->route('companies.index');
    }

    public function destroy(ShippingCompany $company)
    {
        if ($company->shipments()->exists()) {
            Alert::toast('لا يمكن حذف الشركة لوجود شحنات مرتبطة.', 'error');
            return redirect()->route('companies.index');
        }
        $company->delete();
        Alert::toast('تم حذف الشركة بنجاح.', 'success');
        return redirect()->route('companies.index');
    }
}
