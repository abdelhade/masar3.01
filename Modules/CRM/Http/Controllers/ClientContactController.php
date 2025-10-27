<?php

namespace Modules\CRM\Http\Controllers;

use Modules\CRM\Http\Requests\ClientContactRequest;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Models\ClientContact;
use Illuminate\Routing\Controller;

class ClientContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:عرض جهات اتصال الشركات')->only(['index']);
        $this->middleware('can:إضافة جهات اتصال الشركات')->only(['store', 'create']);
        $this->middleware('can:تعديل جهات اتصال الشركات')->only(['edit', 'update']);
        $this->middleware('can:حذف جهات اتصال الشركات')->only(['destory']);
    }

    public function index()
    {
        $ClientContacts = ClientContact::with('client')->paginate(20);
        return view('crm::client-contacts.index', compact('ClientContacts'));
    }

    public function create()
    {
        return view('crm::client-contacts.create');
    }

    public function store(ClientContactRequest $request)
    {
        ClientContact::create($request->validated());
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('client-contacts.index');
    }

    public function show($id)
    {
        return view('crm::show');
    }

    public function edit($id)
    {
        $contact = ClientContact::findOrFail($id);
        return view('crm::client-contacts.edit', compact('contact'));
    }

    public function update(ClientContactRequest $request, $id)
    {
        $contact = ClientContact::findOrFail($id);
        $contact->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('client-contacts.index');
    }

    public function destroy($id)
    {
        $ClientContacts = ClientContact::findOrFail($id);
        $ClientContacts->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('client-contacts.index');
    }
}
