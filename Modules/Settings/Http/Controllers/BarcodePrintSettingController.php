<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Settings\Models\BarcodePrintSetting;
use Modules\Settings\Http\Requests\BarcodePrintSettingRequest;

class BarcodePrintSettingController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $settings = BarcodePrintSetting::where('is_default', true)->firstOrFail();
        return view('settings::barcode-setting.edit', compact('settings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BarcodePrintSettingRequest $request)
    {
        $settings = BarcodePrintSetting::where('is_default', true)->firstOrFail();

        $settings->update($request->validated());

        return redirect()->back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }
}
