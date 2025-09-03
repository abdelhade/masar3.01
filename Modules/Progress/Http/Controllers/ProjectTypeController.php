<?php

namespace Modules\Progress\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use Modules\Progress\Models\ProjectType;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Progress\Http\Requests\ProjectTypeRequest;

class ProjectTypeController extends Controller
{

    public function index()
    {
        $types = ProjectType::paginate(20);
        return view('progress::project-types.index', compact('types'));
    }

    public function create()
    {
        return view('progress::project-types.create');
    }

    public function store(ProjectTypeRequest $request)
    {
        try {
            ProjectType::create($request->validated());
            Alert::toast('تم الاضافه بنجاح', 'success');
            return redirect()->route('project.types.index');
        } catch (Exception) {
            Alert::toast('حدث خطا', 'error');
            return redirect()->route('project.types.index');
        }
    }

    public function edit(ProjectType $projectType)
    {
        return view('progress::project-types.edit', compact('projectType'));
    }

    public function update(ProjectTypeRequest $request, ProjectType $projectType)
    {
        try {
            $projectType->update($request->validated());
            Alert::toast('تم التعديل بنجاح', 'success');
            return redirect()->route('project.types.index');
        } catch (Exception) {
            Alert::toast('حدث خطا', 'error');
            return redirect()->route('project.types.index');
        }
    }

    public function destroy(ProjectType $projectType)
    {
        try {
            $projectType->delete();
            Alert::toast('تم الحذف بنجاح', 'success');
            return redirect()->route('project.types.index');
        } catch (Exception) {
            Alert::toast('حدث خطا', 'error');
            return redirect()->route('project.types.index');
        }
    }
}
