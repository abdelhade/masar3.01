<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Settings\Models\Category;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Settings\Models\PublicSetting;
use Illuminate\Routing\Controller;


class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cateries = Category::with('publicSettings')->get();
        $publicSettings = PublicSetting::with('category')->get();
        return view('settings::settings.index', get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('settings::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('settings::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        foreach ($request->input('settings', []) as $key => $value) {
            PublicSetting::where('key', $key)->update(['value' => $value]);
        }
        Alert::toast('تم تحديث الإعدادات بنجاح', 'success');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
