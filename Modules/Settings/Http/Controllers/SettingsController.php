<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\Category;
use Modules\Settings\Models\PublicSetting;
use RealRashid\SweetAlert\Facades\Alert;

class SettingsController extends Controller
{
    /**
     * Apply permissions middleware
     */
    // public function __construct()
    // {
    //     $this->middleware('permission:view General Settings')->only(['index']);
    //     $this->middleware('permission:edit General Settings')->only(['update']);
    // }

    /**
     * Display general settings page
     */
    public function index()
    {
        $categories = Category::with('publicSettings')->get();
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


    public function update(Request $request)
    {
        try {
            $settings = $request->input('settings', []);

            if (empty($settings)) {
                Alert::toast(__('No settings to update'), 'warning');
                return redirect()->back();
            }

            foreach ($settings as $key => $value) {
                PublicSetting::where('key', $key)->update([
                    'value' => is_array($value) ? json_encode($value) : trim($value)
                ]);
            }

            // Clear settings cache
            Cache::forget('public_settings');

            Alert::toast(__('Settings updated successfully'), 'success');
            return redirect()->back();
        } catch (\Exception) {
            Alert::toast(__('An error occurred while updating settings'), 'error');
            return redirect()->back();
        }
    }
}
