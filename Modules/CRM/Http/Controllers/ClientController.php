<?php

namespace Modules\CRM\Http\Controllers;

use Modules\CRM\Models\CrmClient;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\CrmClientRequest;
use Illuminate\Routing\Controller;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:view clients crm')->only(['index']);
    }

    public function index()
    {
        $clients = CrmClient::with(['contacts', 'leads'])->paginate(20);
        return view('crm::clients.index', compact('clients'));
    }

    public function create()
    {
        return view('crm::clients.create');
    }

    public function store(CrmClientRequest $request)
    {
        CrmClient::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'phone'      => $request->phone,
            'email'      => $request->email,
            'address'    => $request->address,
            'notes'      => $request->notes,
            'created_by' => Auth::id(),
        ]);
        Alert::toast('تم إنشاء العميل بنجاح', 'success');
        return redirect()->route('clients.index');
    }

    public function show($id)
    {
        // return view('crm::show');
    }

    public function edit($id)
    {
        $client = CrmClient::findOrFail($id);
        return view('crm::clients.edit', compact('client'));
    }

    public function update(CrmClientRequest $request, $id)
    {
        $client = CrmClient::findOrFail($id);

        $client->update($request->validated());
        Alert::toast('تم تعديل العميل بنجاح', 'success');
        return redirect()->route('clients.index');
    }

    public function destroy($id)
    {
        $client = CrmClient::findOrFail($id);
        $client->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('clients.index');
    }
}
