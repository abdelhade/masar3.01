<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Inquiries\Models\PricingStatus;
use Modules\Inquiries\Http\Requests\PricingStatusRequest;
use RealRashid\SweetAlert\Facades\Alert;

class PricingStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Pricing Statuses')->only(['index']);
        $this->middleware('permission:create Pricing Statuses')->only(['create', 'store']);
        $this->middleware('permission:edit Pricing Statuses')->only(['edit', 'update']);
        $this->middleware('permission:delete Pricing Statuses')->only(['destroy']);
    }

    public function index()
    {
        $pricingStatuses = PricingStatus::paginate(20);
        return view('inquiries::pricing-statuses.index', compact('pricingStatuses'));
    }

    public function create()
    {
        return view('inquiries::pricing-statuses.create');
    }

    public function store(PricingStatusRequest $request)
    {
        PricingStatus::create($request->validated());
        Alert::toast(__('Item created successfully'), 'success');
        return redirect()->route('pricing-statuses.index');
    }

    public function edit($id)
    {
        $pricingStatus = PricingStatus::findOrFail($id);
        return view('inquiries::pricing-statuses.edit', compact('pricingStatus'));
    }

    public function update(PricingStatusRequest $request, PricingStatus $pricingStatus)
    {
        try {

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $pricingStatus->update($data);
            Alert::toast(__('Item updated successfully'), 'success');
            return redirect()->route('pricing-statuses.index');
        } catch (\Exception $e) {
            Alert::toast(__('Error edit item'), 'error');
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $pricingStatus = PricingStatus::findOrFail($id);

            // التحقق من عدم وجود inquiries مرتبطة
            if ($pricingStatus->inquiries()->count() > 0) {
                Alert::toast(__('Cannot delete: Pricing Status is in use'), 'error');
                return redirect()->back();
            }

            $pricingStatus->delete();
            Alert::toast(__('Item deleted successfully'), 'success');
            return redirect()->route('pricing-statuses.index');
        } catch (\Exception $e) {
            Alert::toast(__('Error deleting item'), 'error');
            return redirect()->back();
        }
    }
}
