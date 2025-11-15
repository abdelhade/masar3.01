<?php

namespace Modules\CRM\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CRM\Http\Requests\ClientContactRequest;
use Modules\CRM\Models\ClientContact;
use RealRashid\SweetAlert\Facades\Alert;

class ClientContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Client Contacts')->only(['index']);
        $this->middleware('can:create Client Contacts')->only(['create', 'store']);
        $this->middleware('can:edit Client Contacts')->only(['edit', 'update']);
        $this->middleware('can:delete Client Contacts')->only(['destroy']);
    }

    public function index()
    {
        $clientContacts = ClientContact::with('client')->paginate(20);
        return view('crm::client-contacts.index', compact('clientContacts'));
    }

    public function create()
    {
        return view('crm::client-contacts.create');
    }

    public function store(ClientContactRequest $request)
    {
        ClientContact::create($request->validated());
        Alert::toast(__('Contact created successfully'), 'success');
        return redirect()->route('client-contacts.index');
    }
    public function show($id)
    {
        return view('crm::show');
    }
    public function edit(ClientContact $contact)
    {
        return view('crm::client-contacts.edit', compact('contact'));
    }

    public function update(ClientContactRequest $request, ClientContact $contact)
    {
        $contact->update($request->validated());
        Alert::toast(__('Contact updated successfully'), 'success');
        return redirect()->route('client-contacts.index');
    }

    public function destroy(ClientContact $contact)
    {
        try {
            $contact->delete();
            Alert::toast(__('Contact deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the contact'), 'error');
        }
        return redirect()->route('client-contacts.index');
    }
}
