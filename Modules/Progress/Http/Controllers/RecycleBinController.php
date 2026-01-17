<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Progress\Models\ProjectProgress;
use Modules\Progress\Models\Issue;
use Modules\Progress\Models\DailyProgress;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\WorkItemCategory;
use Modules\Progress\Models\ItemStatus;
use Modules\Progress\Models\Subproject;

class RecycleBinController extends Controller
{
           public function __construct()
    {
        $this->middleware('can:view progress-recyclebin')->only('index');
      
    }
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'projects');

        $models = [
            'projects' => ProjectProgress::class,
            'issues' => Issue::class,
            'daily_progress' => DailyProgress::class,
            'project_types' => ProjectType::class,
            'project_templates' => ProjectTemplate::class,
            'work_items' => WorkItem::class,
            'categories' => WorkItemCategory::class,
            'statuses' => ItemStatus::class,
            'subprojects' => Subproject::class,
        ];

        if (!array_key_exists($tab, $models)) {
            $tab = 'projects';
        }

        $items = $models[$tab]::onlyTrashed()->paginate(10)->withQueryString();

        return view('progress::recycle_bin.index', compact('items', 'tab'));
    }

    public function restore($type, $id)
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return redirect()->back()->with('error', __('Invalid Item Type'));
        }

        $item = $modelClass::onlyTrashed()->find($id);
        if ($item) {
            $item->restore();
            return redirect()->back()->with('success', __('Item restored successfully'));
        }

        return redirect()->back()->with('error', __('Item not found'));
    }

    public function forceDelete($type, $id)
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return redirect()->back()->with('error', __('Invalid Item Type'));
        }

        $item = $modelClass::onlyTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
            return redirect()->back()->with('success', __('Item permanently deleted'));
        }

        return redirect()->back()->with('error', __('Item not found'));
    }

    protected function getModelClass($type)
    {
        $map = [
            'projects' => ProjectProgress::class,
            'issues' => Issue::class,
            'daily_progress' => DailyProgress::class,
            'project_types' => ProjectType::class,
            'project_templates' => ProjectTemplate::class,
            'work_items' => WorkItem::class,
            'categories' => WorkItemCategory::class,
            'statuses' => ItemStatus::class,
            'subprojects' => Subproject::class,
        ];

        return $map[$type] ?? null;
    }
}
