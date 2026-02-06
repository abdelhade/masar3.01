<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Progress\Models\Project;
use Modules\Progress\Models\DailyProgress;
use Modules\Progress\Models\Client;
use Modules\Progress\Models\Employee;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\ProjectType;

class RecycleBinController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view recycle-bin')->only(['index']);
        $this->middleware('can:restore recycle-bin')->only(['restore']);
        $this->middleware('can:delete recycle-bin')->only(['forceDelete', 'permanentDelete']);
    }

    public function index()
    {
        $deletedProjects = Project::onlyTrashed()->get();
        $deletedDailyProgress = DailyProgress::onlyTrashed()->get();
        $deletedClients = Client::onlyTrashed()->get();
        $deletedEmployees = Employee::onlyTrashed()->get();
        $deletedWorkItems = WorkItem::onlyTrashed()->get();
        $deletedTemplates = ProjectTemplate::onlyTrashed()->get();
        $deletedTypes = ProjectType::onlyTrashed()->get();

        return view('progress::recycle-bin.index', compact(
            'deletedProjects',
            'deletedDailyProgress',
            'deletedClients',
            'deletedEmployees',
            'deletedWorkItems',
            'deletedTemplates',
            'deletedTypes'
        ));
    }

    public function restore($type, $id)
    {
        $model = $this->getModel($type);

        if ($model) {
            $item = $model::onlyTrashed()->findOrFail($id);
            $item->restore();
            return redirect()->back()->with('success', 'تم استعادة العنصر بنجاح');
        }

        return redirect()->back()->with('error', 'نوع العنصر غير صحيح');
    }

    public function forceDelete($type, $id)
    {
        $model = $this->getModel($type);

        if ($model) {
            $item = $model::onlyTrashed()->findOrFail($id);
            $item->forceDelete();
            return redirect()->back()->with('success', 'تم حذف العنصر نهائياً بنجاح');
        }

        return redirect()->back()->with('error', 'نوع العنصر غير صحيح');
    }

    private function getModel($type)
    {
        $models = [
            'project' => Project::class,
            'daily-progress' => DailyProgress::class,
            'client' => Client::class,
            'employee' => Employee::class,
            'work-item' => WorkItem::class,
            'template' => ProjectTemplate::class,
            'type' => ProjectType::class,
        ];

        return $models[$type] ?? null;
    }
}
