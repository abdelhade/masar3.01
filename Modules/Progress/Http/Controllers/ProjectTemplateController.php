<?php

namespace Modules\Progress\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Progress\Models\WorkItem;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Progress\Models\TemplateItem;
use Modules\Progress\Models\ProjectTemplate;
use Modules\Progress\Http\Requests\ProjectTemplateRequest;

class ProjectTemplateController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:project-templates-list')->only('index');
    //     $this->middleware('can:project-templates-create')->only(['create', 'store']);
    //     $this->middleware('can:project-templates-edit')->only(['edit', 'update']);
    //     $this->middleware('can:project-templates-delete')->only('destroy');
    //     $this->middleware('can:project-templates-view')->only('show');
    // }

    public function index()
    {
        $templates = ProjectTemplate::withCount('items')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('progress::project-templates.index', compact('templates'));
    }

    public function create()
    {
        $workItems = WorkItem::all();
        return view('progress::project-templates.create', compact('workItems'));
    }

    public function store(ProjectTemplateRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $template = ProjectTemplate::create([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);

                foreach ($request->items as $item) {
                    $template->items()->create([
                        'work_item_id' => $item['work_item_id'],
                        'default_quantity' => $item['default_quantity'] ?? 1,
                    ]);
                }
            });

            Alert::toast('تم إنشاء القالب والبنود بنجاح', 'success');
            return redirect()->route('project.template.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء الإضافة', 'error');
            return redirect()->route('project.template.index');
        }
    }

    public function show(ProjectTemplate $projectTemplate)
    {
        $projectTemplate->load('items');
        return view('progress::project-templates.show', compact('projectTemplate'));
    }

    public function edit(ProjectTemplate $project_template)
    {
        $project_template->load('items');

        $initialItems = $project_template->items->map(function ($item) {
            return [
                'id' => $item->id,
                'work_item_id' => $item->work_item_id,
                'default_quantity' => $item->default_quantity,
            ];
        });

        $workItems = WorkItem::all();

        return view('progress::project-templates.edit', [
            'projectTemplate' => $project_template,
            'initialItems' => $initialItems->toJson(),
            'workItems' => $workItems,
        ]);
    }


    public function update(ProjectTemplateRequest $request, ProjectTemplate $project_template)
    {
        try {
            DB::transaction(function () use ($request, $project_template) {
                $project_template->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);

                $keptIds = [];
                foreach ($request->items as $item) {
                    if (!empty($item['id'])) {
                        $ti = TemplateItem::where('id', $item['id'])
                            ->where('project_template_id', $project_template->id)
                            ->firstOrFail();

                        $ti->update([
                            'work_item_id' => $item['work_item_id'],
                            'default_quantity' => $item['default_quantity'] ?? 1,
                        ]);
                        $keptIds[] = $ti->id;
                    } else {
                        $ti = $project_template->items()->create([
                            'work_item_id' => $item['work_item_id'],
                            'default_quantity' => $item['default_quantity'] ?? 1,
                        ]);
                        $keptIds[] = $ti->id;
                    }
                }

                TemplateItem::where('project_template_id', $project_template->id)
                    ->when(count($keptIds) > 0, fn($q) => $q->whereNotIn('id', $keptIds))
                    ->delete();
            });
            Alert::toast('تم تحديث القالب والبنود بنجاح', 'success');
            return redirect()->route('project.template.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء التعديل', 'error');
            return redirect()->route('project.template.index');
        }
    }

    public function destroy(ProjectTemplate $project_template)
    {
        try {
            $project_template->delete();
            Alert::toast('تم حذف القالب بنجاح', 'success');
            return redirect()->route('project.template.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء الحذف', 'error');
            return redirect()->route('project.template.index');
        }
    }

    public function items(ProjectTemplate $project_template)
    {
        $items = $project_template->items()
            ->select('id', 'name', 'unit', 'default_quantity')
            ->orderBy('id')
            ->get();

        return response()->json([
            'template_id' => $project_template->id,
            'template_name' => $project_template->name,
            'items' => $items,
        ]);
    }
}
