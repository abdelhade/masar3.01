<?php

namespace Modules\Progress\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Progress\Models\WorkItem;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\ProjectType;
use Modules\Progress\Models\ProjectProgress;
use Modules\Progress\Models\ProjectTemplate;

class ProjectProgressController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('can:projects-list')->only('index');
    //     $this->middleware('can:projects-create')->only(['create', 'store']);
    //     $this->middleware('can:projects-edit')->only(['edit', 'update']);
    //     $this->middleware('can:projects-delete')->only('destroy');
    //     $this->middleware('can:projects-view')->only('show');
    //     // $this->middleware('can:projects-progress')->only('progress');
    // }

    public function index()
    {
        $employee = Auth::user()->employee;

        // if (!$employee) {
        //     $projects = collect();
        // } else {
        $projects = ProjectProgress::with('client')
            ->withCount('items')
            ->latest()
            ->get();
        // }
        return view('progress::projects.index', compact('projects'));
    }

    public function create()
    {
        $clients = Client::all();
        $workItems = WorkItem::all();
        $employees = Employee::all();
        $templates = ProjectTemplate::with(['items.workItem'])->get();
        $projectTypes = ProjectType::all();

        $templates = $templates->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'items' => $template->items->map(function ($it) {
                    return [
                        'work_item_id' => $it->work_item_id,
                        'name' => $it->workItem?->name ?? 'غير محدد',
                        'unit' => $it->workItem?->unit ?? '',
                        'default_quantity' => $it->default_quantity,
                    ];
                })->values(),
            ];
        });

        return view('progress::projects.create', compact('clients', 'workItems', 'employees', 'templates', 'projectTypes'));
    }

    public function store(Request $request)
    {
        $maxDuration = 0;
        foreach ($request['items'] as $item) {
            $duration = ceil($item['total_quantity'] / $item['estimated_daily_qty']);
            if ($duration > $maxDuration) {
                $maxDuration = $duration;
            }
        }

        $endDate = $maxDuration > 0
            ? Carbon::parse($request['start_date'])->addDays($maxDuration)
            : null;

        // إنشاء المشروع
        $project = ProjectProgress::create([
            'name' => $request['name'],
            'description' => $request['description'] ?? null,
            'client_id' => $request['client_id'],
            'start_date' => $request['start_date'],
            'end_date' => $endDate,
            'status' => $request['status'],
            'working_zone' => $request['working_zone'],
            'project_type_id' => $request['project_type_id'],
            'working_days' => $request['working_days'],
            'daily_work_hours' => $request['daily_work_hours'],
            'holidays' => $request['holidays'] ?? 0,
        ]);

        // إضافة بنود المشروع
        foreach ($request['items'] as $item) {
            $plannedEndDate = Carbon::parse($request['start_date'])
                ->addDays(ceil($item['total_quantity'] / $item['estimated_daily_qty']));

            $project->items()->create([
                'project_id' => $project->id,
                'work_item_id' => $item['work_item_id'],
                'total_quantity' => $item['total_quantity'],
                'estimated_daily_qty' => $item['estimated_daily_qty'],
                'completed_quantity' => 0,
                'remaining_quantity' => $item['total_quantity'],
                'start_date' => $request['start_date'],
                'end_date' => $plannedEndDate,
                'planned_end_date' => $plannedEndDate,
            ]);
        }

        // ربط الموظفين بالمشروع
        $project->employees()->sync($request['employees']);

        return redirect()
            ->route('progress.projcet.show', $project->id)
            ->with('success', 'تم إضافة المشروع بنجاح');
    }


    public function show($id)
    {
        // جلب المشروع مع العلاقات مباشرة
        $project = ProjectProgress::with([
            'client',
            'items' => function ($query) {
                $query->withSum('dailyProgress', 'quantity');
            },
            'items.workItem',
            'items.dailyProgress' => function ($query) {
                $query->latest('progress_date')->limit(10);
            },
            'items.dailyProgress.employee',
            'dailyProgress' => function ($query) {
                $query->latest('progress_date')->limit(5);
            },
            'dailyProgress.employee',
            'dailyProgress.projectItem.workItem'
        ])->findOrFail($id);

        // حساب التقدم
        $totalQuantity = 0;
        $totalCompleted = 0;

        foreach ($project->items as $item) {
            // استخدام المجموع المحسوب مسبقاً
            $completedQuantity = $item->daily_progress_sum_quantity ?? 0;
            $item->completed_quantity = $completedQuantity;
            $item->remaining_quantity = max(0, $item->total_quantity - $completedQuantity);

            $completionPercentage = $item->total_quantity > 0
                ? min(100, ($completedQuantity / $item->total_quantity) * 100)
                : 0;

            $item->completion_percentage = round($completionPercentage, 2);

            $totalQuantity += $item->total_quantity;
            $totalCompleted += $completedQuantity;
        }

        $overallProgress = $totalQuantity > 0
            ? round(min(100, ($totalCompleted / $totalQuantity) * 100), 2)
            : 0;

        // جلب الموظفين
        $employees = Employee::all();
        if ($employees->isEmpty()) {
            $employees = collect([new Employee(['name' => 'لا يوجد موظفين'])]);
        }

        return view('progress::projects.show', compact('project', 'employees', 'overallProgress'));
    }

    public function edit(ProjectProgress $project)
    {
        $initialItems = $project->items->map(function ($item) {
            return [
                'work_item_id' => $item->work_item_id,
                'total_quantity' => $item->total_quantity,
                'name' => $item->workItem->name,
                'unit' => $item->workItem->unit,
            ];
        })->values()->all();

        $project->load('items.workItem', 'employees');
        $clients = Client::all();
        $workItems = WorkItem::all();
        $employees = Employee::all();
        $templates = ProjectTemplate::all();
        $projectTypes = ProjectType::all();

        return view('progress::projects.edit', compact(
            'project',
            'clients',
            'workItems',
            'employees',
            'templates',
            'initialItems',
            'projectTypes'
        ));
    }

    public function update(Request $request, ProjectProgress $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:active,completed,pending',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'working_zone' => 'required|string|max:255',
            'project_type' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.work_item_id' => 'required|exists:work_items,id',
            'items.*.total_quantity' => 'required|numeric|min:0.01',
            'project_type_id' => 'required|exists:project_types,id',

        ]);

        // تحديث بيانات المشروع
        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'client_id' => $validated['client_id'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'working_zone' => $validated['working_zone'],
            'project_type_id' => $validated['project_type_id'], // ✅ أهو اللي ناقص
        ]);

        // تحديث الموظفين المرتبطين
        if ($request->has('employees')) {
            $project->employees()->sync($request->employees);
        }

        // البنود الحالية في المشروع
        $existingItems = $project->items->keyBy('work_item_id');

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $days = $startDate && $endDate
            ? Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1
            : 1;

        // تحديث أو إضافة البنود
        foreach ($request->items as $item) {
            $dailyQuantity = $item['total_quantity'] / max($days, 1);

            if ($existingItems->has($item['work_item_id'])) {
                // تحديث البند الموجود
                $existingItems[$item['work_item_id']]->update([
                    'total_quantity' => $item['total_quantity'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'daily_quantity' => round($dailyQuantity, 2),
                ]);
            } else {
                // إضافة بند جديد
                ProjectItem::create([
                    'project_id' => $project->id,
                    'work_item_id' => $item['work_item_id'],
                    'total_quantity' => $item['total_quantity'],
                    'completed_quantity' => 0,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'daily_quantity' => round($dailyQuantity, 2),
                ]);
            }
        }

        // حذف البنود اللي اتشالت من الفورم
        $project->items()
            ->whereNotIn('work_item_id', collect($request->items)->pluck('work_item_id'))
            ->delete();

        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', 'تم تحديث المشروع بنجاح');
    }

    public function destroy(ProjectProgress $project)
    {
        // Start database transaction
        DB::beginTransaction();

        try {
            // Delete all related daily progress records first
            $project->items()->each(function ($item) {
                if (method_exists($item, 'dailyProgress')) {
                    $item->dailyProgress()->delete();
                }
                $item->delete();
            });

            // Then delete the project
            $project->delete();

            // Commit transaction if all deletions succeeded
            DB::commit();

            return redirect()
                ->route('projects.index')
                ->with('success', 'تم حذف المشروع وكل البيانات المرتبطة به بنجاح');
        } catch (\Exception $e) {
            // Rollback transaction if any error occurs
            DB::rollBack();

            return back()
                ->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    public function progress(ProjectProgress $project, Request $request)
    {
        // التحقق من الصلاحيات أولاً
        // $this->authorize('view', $project);

        // استخدام تاريخ اليوم كقيمة افتراضية لـ to_date
        $toDate = $request->input('to_date', Carbon::today()->format('Y-m-d'));

        // استخدام أسبوع قبل to_date كقيمة افتراضية لـ from_date
        $fromDate = $request->input('from_date', Carbon::parse($toDate)->subWeek()->format('Y-m-d'));

        // تحميل المشروع مع العناصر المرتبطة فقط
        $project->load(['client', 'items.workItem']);

        // تحميل التقدم اليومي مع فلترة حسب التاريخ
        $project->items->each(function ($item) use ($fromDate, $toDate) {
            // التقدم خلال الفترة المحددة (من from_date إلى to_date)
            $item->period_progress = max($item->dailyProgress()
                ->whereBetween('progress_date', [$fromDate, $toDate])
                ->sum('quantity'), 0); // منع القيم السالبة

            // التقدم قبل from_date (التراكمي حتى from_date)
            $item->previous_progress = max($item->dailyProgress()
                ->where('progress_date', '<', $fromDate)
                ->sum('quantity'), 0); // منع القيم السالبة

            // التقدم الكلي حتى to_date
            $item->total_completed = max($item->dailyProgress()
                ->where('progress_date', '<=', $toDate)
                ->sum('quantity'), 0); // منع القيم السالبة

            // التأكد من أن total_completed = previous_progress + period_progress
            // (قد يكون هناك اختلاف بسيط بسبب التواريخ)
            if ($item->total_completed != ($item->previous_progress + $item->period_progress)) {
                $item->total_completed = $item->previous_progress + $item->period_progress;
            }

            // نسبة الإنجاز
            $item->progress_percentage = $item->total_quantity > 0
                ? min(round(($item->total_completed / $item->total_quantity) * 100, 2), 100) // عدم تجاوز 100%
                : 0;
        });

        // حساب التقدم العام للمشروع
        $totalProjectQuantity = $project->items->sum('total_quantity');
        $totalCompletedQuantity = $project->items->sum('total_completed');
        $projectProgress = $totalProjectQuantity > 0
            ? min(round(($totalCompletedQuantity / $totalProjectQuantity) * 100, 2), 100) // عدم تجاوز 100%
            : 0;

        return view('progress::projects.progress', compact('project', 'projectProgress', 'fromDate', 'toDate'));
    }
}
