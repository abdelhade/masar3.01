<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Models\ClientType;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\ClientTypeRequest;

class ClientTypeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:عرض انواع العملاء')->only(['index']);
    //     $this->middleware('can:إضافة انواع العملاء')->only(['create', 'store']);
    //     $this->middleware('can:تعديل انواع العملاء')->only(['edit', 'update']);
    //     $this->middleware('can:حذف انواع العملاء')->only(['destroy']);
    // }

    public function index()
    {
        $customerTypes = ClientType::all();
        return view('crm::client-type.index', compact('customerTypes'));
    }

    public function create()
    {
        $branches = userBranches();
        return view('crm::client-type.create', compact('branches'));
    }

    public function store(ClientTypeRequest $request)
    {
        ClientType::create($request->validated());
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('client-types.index');
    }

    public function edit($id)
    {
        $customerType = ClientType::findOrFail($id);
        return view('crm::client-type.edit', compact('customerType'));
    }

    public function update(ClientTypeRequest $request, $id)
    {
        $customerType = ClientType::findOrFail($id);
        $customerType->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('client-types.index');
    }

    public function destroy($id)
    {
        $customerType = ClientType::findOrFail($id);
        $customerType->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('client-types.index');
    }
}
