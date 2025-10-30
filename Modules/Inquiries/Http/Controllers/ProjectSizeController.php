<?php

namespace Modules\Inquiries\Http\Controllers;

use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\ProjectSize;
use Modules\Inquiries\Http\Requests\ProjectSizeRequest;

class ProjectSizeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:عرض حجم المشروع')->only(['index']);
    //     $this->middleware('can:إضافة حجم المشروع')->only(['create', 'store']);
    //     $this->middleware('can:تعديل حجم المشروع')->only(['edit', 'update']);
    //     $this->middleware('can:حذف حجم المشروع')->only(['destroy']);
    // }

    public function index()
    {
        try {
            $projectSizes = ProjectSize::all();
            return view('inquiries::project-size.index', compact('projectSizes'));
        } catch (\Exception $e) {
            Alert::toast(__('Error loading project sizes'), 'error');
            return redirect()->back();
        }
    }

    public function create()
    {
        return view('inquiries::project-size.create');
    }

    public function store(ProjectSizeRequest $request)
    {
        try {
            ProjectSize::create($request->validated());
            Alert::toast(__('Project size created successfully'), 'success');
            return redirect()->route('project-size.index');
        } catch (\Exception $e) {
            Alert::toast(__('Error during project size creation'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $projectSize = ProjectSize::findOrFail($id);
            return view('inquiries::project-size.edit', compact('projectSize'));
        } catch (\Exception $e) {
            Alert::toast(__('Project size not found'), 'error');
            return redirect()->route('project-size.index');
        }
    }

    public function update(ProjectSizeRequest $request, $id)
    {
        try {
            $projectSize = ProjectSize::findOrFail($id);
            $projectSize->update($request->validated());
            Alert::toast(__('Project size updated successfully'), 'success');
            return redirect()->route('project-size.index');
        } catch (\Exception $e) {
            Alert::toast(__('Error during project size update'), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $projectSize = ProjectSize::findOrFail($id);
            $projectSize->delete();
            Alert::toast(__('Project size deleted successfully'), 'success');
            return redirect()->route('project-size.index');
        } catch (\Exception $e) {
            Alert::toast(__('Error during project size deletion'), 'error');
            return redirect()->back();
        }
    }
}
