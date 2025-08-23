<?php

namespace Modules\CRM\Http\Controllers;

use App\Models\User;
use Modules\CRM\Models\{Activity, CrmClient};
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\CRM\Http\Requests\ActivityRequest;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activity::with(['client', 'assignedUser'])->latest()->paginate(20);
        return view('crm::activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = CrmClient::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        return view('crm::activities.create', compact('clients', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ActivityRequest $request)
    {
        try {
            Activity::create($request->validated());
            Alert::toast('تم إضافة النشاط بنجاح ✅', 'success');
            return redirect()->route('activities.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء التحقق من البيانات', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // $activity = Activity::findOrFail($id);
        // return view('crm::activities.show', compact('activity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        $clients = CrmClient::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        return view('crm::activities.edit', compact('activity', 'clients', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ActivityRequest $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $activity->update($request->validated());
            Alert::toast('تم تحديث النشاط بنجاح ✏️', 'success');
            return redirect()->route('activities.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء التحقق من البيانات', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $activity->delete();
            Alert::toast('تم حذف النشاط 🗑️', 'success');
            return redirect()->route('activities.index');
        } catch (\Exception $e) {
            Alert::toast('النشاط غير موجود', 'error');
            return redirect()->route('activities.index');
        }
    }
}
