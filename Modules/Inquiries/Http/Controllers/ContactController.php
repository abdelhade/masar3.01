<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Inquiries\Models\Contact;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\InquirieRole;
use Modules\Inquiries\Http\Requests\ContactRequest;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view Contacts')->only(['index']);
        $this->middleware('permission:create Contacts')->only(['create', 'store']);
        $this->middleware('permission:edit Contacts')->only(['edit', 'update']);
        $this->middleware('permission:delete Contacts')->only(['destroy']);
    }

    public function index()
    {
        $contacts = Contact::with(['role', 'parent', 'roles', 'companies', 'persons'])->get();
        return view('inquiries::contacts.index', compact('contacts'));
    }

    public function create()
    {
        $roles = InquirieRole::all();
        $allContacts = Contact::all();
        return view('inquiries::contacts.create', compact('roles', 'allContacts'));
    }

    public function store(ContactRequest $request)
    {
        DB::beginTransaction();
        try {
            $contact = Contact::create($request->validated());

            // Sync roles
            if ($request->has('roles')) {
                $contact->roles()->sync($request->roles);
            }

            // Sync related contacts
            if ($request->has('related_contacts')) {
                if ($contact->type === 'person') {
                    $contact->companies()->sync($request->related_contacts);
                } else {
                    $contact->persons()->sync($request->related_contacts);
                }
            }

            DB::commit();
            Alert::toast(__('Item created successfully'), 'success');
            return redirect()->route('contacts.index');
        } catch (\Exception) {
            DB::rollBack();
            Alert::toast(__('Error creating contact'), 'error');
            return back()->withInput();
        }
    }

    public function show($id)
    {
        return view('inquiries::contacts.show');
    }

    public function edit($id)
    {
        $contact = Contact::with(['roles', 'companies', 'persons'])->findOrFail($id);
        $roles = InquirieRole::all();
        $allContacts = Contact::where('id', '!=', $id)->get();
        return view('inquiries::contacts.edit', compact('contact', 'roles', 'allContacts'));
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        DB::beginTransaction();
        try {
            $contact->update($request->validated());

            // Sync roles - حتى لو فاضي هيمسح القديم
            $contact->roles()->sync($request->input('roles', []));

            // Sync related contacts - الإصلاح هنا
            $relatedContacts = $request->input('related_contacts', []);

            if ($contact->type === 'person') {
                // لو شخص، نربطه بالشركات
                $contact->companies()->sync($relatedContacts);
            } else {
                // لو شركة، نربطها بالأشخاص
                $contact->persons()->sync($relatedContacts);
            }

            DB::commit();
            Alert::toast(__('Item updated successfully'), 'success');
            return redirect()->route('contacts.index');
        } catch (\Exception) {
            DB::rollBack();
            Alert::toast(__('Error updating contact') . ': ', 'error');
            return back()->withInput();
        }
    }


    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contact->delete();
            Alert::toast(__('Item deleted successfully'), 'success');
            return redirect()->route('contacts.index');
        } catch (\Exception) {
            Alert::toast(__('Error deleting contact'), 'error');
            return redirect()->back();
        }
    }
}
