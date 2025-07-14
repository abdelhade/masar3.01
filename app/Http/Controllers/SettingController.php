<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first(); // الجدول يحتوي على صف واحد فقط
        return view('settings.index', compact('setting'));
    }

    public function edit($id)
    {
        $setting = Setting::findOrFail($id);
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:200',
            'company_add' => 'nullable|string|max:200',
            'company_email' => 'nullable|email|max:50',
            'company_tel' => 'nullable|string|max:200',
            'edit_pass' => 'nullable|string|max:50',
            'lic' => 'nullable|string|max:250',
            'updateline' => 'nullable|string',
            'acc_rent' => 'nullable|integer',
            'startdate' => 'nullable|date',
            'enddate' => 'nullable|date',
            'lang' => 'nullable|string|max:20',
            'bodycolor' => 'nullable|string|max:50',
            'showhr' => 'nullable|integer',
            'showclinc' => 'nullable|integer',
            'showatt' => 'nullable|integer',
            'showpayroll' => 'nullable|integer',
            'showrent' => 'nullable|integer',
            'showpay' => 'nullable|integer',
            'showtsk' => 'nullable|integer',
            'def_pos_client' => 'nullable|integer',
            'def_pos_store' => 'nullable|integer',
            'def_pos_employee' => 'nullable|integer',
            'def_pos_fund' => 'nullable|integer',
            'isdeleted' => 'nullable|boolean',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
            'show_all_tasks' => 'nullable|integer',
            'logo' => 'nullable|string|max:255',
        ]);

        $setting->update($validated);

        return redirect()->route('settings.index')->with('success', 'تم تحديث الإعدادات بنجاح');
    }
}
