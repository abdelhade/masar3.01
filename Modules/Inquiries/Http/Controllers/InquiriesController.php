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
            'workType', // الـ work type الرئيسي
            'workTypes', // ⭐ كل الـ work types (Many-to-Many)
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
            'comments.user',
            'media'
        ])->findOrFail($id);

        // ⭐ بناء المسار الهرمي للـ Work Type الرئيسي (لو موجود)
        $workTypePath = [];
        $currentWorkType = $inquiry->workType;
        while ($currentWorkType) {
            $workTypePath[] = $currentWorkType->name;
            $currentWorkType = $currentWorkType->parent;
        }
        $workTypePath = array_reverse($workTypePath);

        // ⭐ بناء قائمة بكل الـ Work Types مع المسارات الهرمية
        $allWorkTypes = [];
        if ($inquiry->workTypes->isNotEmpty()) {
            foreach ($inquiry->workTypes as $workType) {
                $hierarchyPath = json_decode($workType->pivot->hierarchy_path, true);

                if ($hierarchyPath && is_array($hierarchyPath)) {
                    // بناء المسار النصي
                    $pathNames = [];
                    foreach ($hierarchyPath as $stepId) {
                        $wt = \Modules\Inquiries\Models\WorkType::find($stepId);
                        if ($wt) {
                            $pathNames[] = $wt->name;
                        }
                    }

                    $allWorkTypes[] = [
                        'work_type' => $workType,
                        'hierarchy_path' => $pathNames,
                        'description' => $workType->pivot->description ?? '',
                        'order' => $workType->pivot->order ?? 0
                    ];
                }
            }
        }

        // بناء المسار الهرمي للـ Inquiry Source
        $inquirySourcePath = [];
        $currentInquirySource = $inquiry->inquirySource;
        while ($currentInquirySource) {
            $inquirySourcePath[] = $currentInquirySource->name;
            $currentInquirySource = $currentInquirySource->parent;
        }
        $inquirySourcePath = array_reverse($inquirySourcePath);

        return view('inquiries::inquiries.show', compact(
            'inquiry',
            'workTypePath',
            'allWorkTypes', // ⭐ إضافة المتغير الجديد
            'inquirySourcePath'
        ));
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
