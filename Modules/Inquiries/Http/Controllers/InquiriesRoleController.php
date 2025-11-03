<?php

namespace Modules\Inquiries\Http\Controllers;

use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\InquirieRole;
use Modules\Inquiries\Http\Requests\InquiriesRoleRequest;

class InquiriesRoleController extends Controller
{
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
        Alert::toast('تم الانشاء بنجاح', 'success');
        return redirect()->route('inquiries-roles.index');
    }

    public function edit(InquirieRole $inquiries_role)
    {
        return view('inquiries::inquiries-role.edit', compact('inquiries_role'));
    }

    public function update(InquiriesRoleRequest $request, InquirieRole $inquiries_role)
    {
        $inquiries_role->update($request->validated());
        Alert::toast('تم التعديل بنجاح', 'success');
        return redirect()->route('inquiries-roles.index');
    }

    public function show(InquirieRole $inquiries_role)
    {
        return view('inquiries::inquiries-role.show', compact('inquiries_role'));
    }

    public function destroy(InquirieRole $inquiries_role)
    {
        $inquiries_role->delete();
        Alert::toast('تم حذف العنصر بنجاح', 'success');
        return redirect()->route('inquiries-roles.index');
    }
}
