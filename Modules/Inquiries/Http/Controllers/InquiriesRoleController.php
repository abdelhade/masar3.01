<?php

namespace Modules\Inquiries\Http\Controllers;

use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\InquirieRole;
use Modules\Inquiries\Http\Requests\InquiriesRoleRequest;

class InquiriesRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Inquiries Roles')->only('index');
        $this->middleware('can:create Inquiries Roles')->only(['create', 'store']);
        $this->middleware('can:edit Inquiries Roles')->only(['edit', 'update']);
        $this->middleware('can:delete Inquiries Roles')->only('destroy');
    }

    public function index()
    {
        $roles = InquirieRole::latest()->paginate(10);
        return view('inquiries::inquiries-role.index', compact('roles'));
    }

    public function create()
    {
        return view('inquiries::inquiries-role.create');
    }

    public function store(InquiriesRoleRequest $request)
    {
        InquirieRole::create($request->validated());
        Alert::toast('Created successfully', 'success');
        return redirect()->route('inquiries-roles.index');
    }

    public function edit(InquirieRole $inquiries_role)
    {
        return view('inquiries::inquiries-role.edit', compact('inquiries_role'));
    }

    public function update(InquiriesRoleRequest $request, InquirieRole $inquiries_role)
    {
        $inquiries_role->update($request->validated());
        Alert::toast('Updated successfully', 'success');
        return redirect()->route('inquiries-roles.index');
    }

    public function show(InquirieRole $inquiries_role)
    {
        return view('inquiries::inquiries-role.show', compact('inquiries_role'));
    }

    public function destroy(InquirieRole $inquiries_role)
    {
        $inquiries_role->delete();
        Alert::toast('Deleted successfully', 'success');
        return redirect()->route('inquiries-roles.index');
    }
}
