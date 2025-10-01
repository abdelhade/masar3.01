<?php

namespace Modules\Inquiries\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Inquiries\Models\Inquiry;
use RealRashid\SweetAlert\Facades\Alert;

class InquiriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inquiries = Inquiry::with(['project', 'city', 'town', 'client'])->get();
        return view('inquiries::inquiries.index', compact('inquiries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inquiries::inquiries.create');
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
        $inquiry = Inquiry::with([
            'project',
            'workType',
            'inquirySource',
            'client',
            'mainContractor',
            'consultant',
            'owner',
            'assignedEngineer',
            'city',
            'town',
            'projectDocuments',
            'submittalChecklists',
            'workConditions',
            'comments.user', // إضافة التعليقات
            'media'
        ])->findOrFail($id);

        // Build hierarchical paths for work type and inquiry source
        $workTypePath = [];
        $currentWorkType = $inquiry->workType;
        while ($currentWorkType) {
            $workTypePath[] = $currentWorkType->name;
            $currentWorkType = $currentWorkType->parent;
        }
        $workTypePath = array_reverse($workTypePath);

        $inquirySourcePath = [];
        $currentInquirySource = $inquiry->inquirySource;
        while ($currentInquirySource) {
            $inquirySourcePath[] = $currentInquirySource->name;
            $currentInquirySource = $currentInquirySource->parent;
        }
        $inquirySourcePath = array_reverse($inquirySourcePath);

        return view('inquiries::inquiries.show', compact('inquiry', 'workTypePath', 'inquirySourcePath'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('inquiries::inquiries.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $inquiry = Inquiry::findOrFail($id);
            $inquiry->clearMediaCollection();
            $inquiry->submittalChecklists()->detach();
            $inquiry->workConditions()->detach();
            $inquiry->projectDocuments()->detach();
            $inquiry->delete();
            Alert::toast('تم حذف الاستفسار بنجاح.', 'success');
            return redirect()->route('inquiries.index')->with('success', 'تم حذف الاستفسار بنجاح.');
        } catch (Exception) {
            Alert::toast('الاستفسار غير موجود.', 'error');
            return redirect()->route('inquiries.index')->with('error', 'الاستفسار غير موجود.');
        }
    }
}
