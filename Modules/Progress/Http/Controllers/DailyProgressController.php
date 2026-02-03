<?php

namespace Modules\Progress\Http\Controllers;

use Modules\HR\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\DailyProgress;
use Modules\Progress\Models\ProjectProgress;

class DailyProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view daily-progress')->only('index', 'executedToday');
        $this->middleware('can:create daily-progress')->only(['create', 'store']);
        $this->middleware('can:edit daily-progress')->only(['edit', 'update']);
        $this->middleware('can:delete daily-progress')->only('destroy');
    }

    /**
     * التقدم المنفذ اليوم - إما توجيه لصفحة القائمة بتاريخ اليوم أو استجابة JSON
     */
    public function executedToday(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $user = Auth::user();
            $query = DailyProgress::whereDate('progress_date', today());
            if (!$user->hasRole('admin') && !$user->hasRole('super-admin')) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                    if ($user->employee) {
                        $q->orWhere('employee_id', $user->employee->id);
                    }
                });
            }
            $query->whereHas('project', fn ($q) => $q->where('status', '!=', 'draft'));
            return response()->json(['count' => $query->count(), 'date' => today()->toDateString()]);
        }
        return redirect()->route('daily_progress.index', ['progress_date' => today()->toDateString()]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DailyProgress::with(['project', 'projectItem.workItem', 'employee', 'user']);

        // استبعاد المشاريع المسودة (draft)
        $query->whereHas('project', fn ($q) => $q->where('status', '!=', 'draft'));

        // لو الموظف مش أدمن -> يقيد العرض على سجلاته هو فقط (user_id OR employee_id)
        if (!$user->hasRole('admin') && !$user->hasRole('super-admin')) {
             $query->where(function($q) use ($user) {
                 $q->where('user_id', $user->id);
                 if ($user->employee) {
                     $q->orWhere('employee_id', $user->employee->id);
                 }
             });
        }
        
        // قائمة المشاريع للفلتر: كل مشاريع التقدم (حتى تظهر في القائمة؛ السجلات الفعلية تستبعد المسودة)
        $projects = ProjectProgress::select('id', 'name')->orderBy('name')->get();

        // فلترة التاريخ: من - إلى أو تاريخ واحد أو اليوم
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('progress_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('progress_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('progress_date', '<=', $request->to_date);
        } elseif ($request->filled('progress_date')) {
            $query->whereDate('progress_date', $request->progress_date);
        } elseif (!$request->boolean('view_all')) {
            $query->whereDate('progress_date', today());
        }

        // فلترة المشروع
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // جلب البيانات بدون Pagination
        $dailyProgress = $query->orderBy('progress_date', 'desc')->get();

        // التجميع (Grouping)
        // المستوى الأول: المشروع
        $groupedProgress = $dailyProgress->groupBy('project_id')->map(function ($projectGroup) {
            // المستوى الثاني: Subproject
            return $projectGroup->groupBy(function ($item) {
                return $item->projectItem->subproject_name ?? 'عام';
            });
        });

        return view('progress::daily-progress.index', compact('groupedProgress', 'projects'));
    }



    /**
     * فورم إنشاء تقرير يومي جديد
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // قائمة المشاريع لصفحة الإنشاء (نفس مصدر صفحة القائمة)
        $projects = ProjectProgress::select('id', 'name')->orderBy('name')->get();

        // معاملات من الرابط (مثلاً من صفحة Gantt)
        $selectedProjectId = $request->get('project_id');
        $selectedItemId = $request->get('item_id');

        return view('progress::daily-progress.create', compact('projects', 'selectedProjectId', 'selectedItemId'));
    }

    /**
     * حفظ تقرير يومي جديد
     */
    public function store(Request $request)
    {
        $quantities = array_filter($request->input('quantities', []), function ($value) {
            return !is_null($value) && $value !== '';
        });
        $request->merge(['quantities' => $quantities]);

        if (empty($quantities)) {
            return back()->withErrors(['quantities' => __('general.error_no_quantities_entered')])->withInput();
        }

        $validated = $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'progress_date' => 'required|date',
            'quantities'    => 'required|array',
            'quantities.*'  => 'numeric|min:0',
            'notes'         => 'nullable|string'
        ]);

        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            return back()->withInput()->withErrors(['error' => __('general.no_employee_linked') ?? 'لا يوجد موظف مرتبط بحسابك. يرجى التواصل مع المسؤول.']);
        }

        $warnings = [];
        try {
            DB::beginTransaction();

            $project = ProjectProgress::findOrFail($validated['project_id']);
            $hasProgress = false;

            foreach ($validated['quantities'] as $itemId => $qty) {
                if ($qty <= 0) continue;

                $item = ProjectItem::find($itemId);
                if (!$item) continue;

                $currentCompleted = $item->dailyProgress()->sum('quantity');
                $newCompleted = $currentCompleted + $qty;
                $remaining = $item->total_quantity - $currentCompleted;

                if ($qty > $remaining && $remaining > 0) {
                    $warnings[] = __('general.warning_quantity_exceeds_remaining', [
                        'qty' => $qty,
                        'remaining' => $remaining,
                        'item' => $item->workItem->name ?? $itemId
                    ]) ?: "تحذير: الكمية ({$qty}) تتجاوز المتبقي ({$remaining}) للبند: " . ($item->workItem->name ?? $itemId);
                }

                $completionPercentage = $item->total_quantity > 0
                    ? min(100, round(($newCompleted / $item->total_quantity) * 100, 2))
                    : 0;

                DailyProgress::create([
                    'project_id'             => $validated['project_id'],
                    'project_item_id'        => $itemId,
                    'progress_date'          => $validated['progress_date'],
                    'quantity'               => $qty,
                    'notes'                  => $validated['notes'] ?? null,
                    'employee_id'            => $employee->id,
                    'user_id'                => Auth::id(),
                    'branch_id'              => Auth::user()->branch_id ?? 1,
                    'completion_percentage'  => $completionPercentage
                ]);

                $item->update([
                    'completed_quantity'    => $newCompleted,
                    'completion_percentage' => $completionPercentage
                ]);
                $hasProgress = true;
            }

            if (!$hasProgress) {
                throw new \Exception(__('general.error_at_least_one_quantity') ?? 'يرجى إدخال كمية واحدة على الأقل');
            }

            $this->updateProjectStatus($project->id);
            DB::commit();

            if (!empty($warnings)) {
                session()->flash('warning', implode('<br>', $warnings));
            }
            return redirect()->route('daily_progress.index', ['progress_date' => $validated['progress_date']])
                ->with('success', 'تم تسجيل التقدم اليومي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * فورم تعديل تقرير
     */
    public function edit(DailyProgress $dailyProgress)
    {
        // كل المشاريع
        $projects = ProjectProgress::select('id', 'name')->get();

        // كل الموظفين
        $employees = Employee::select('id', 'name')->get();

        // كل البنود (مربوطة بالمشاريع)
        $projectItems = $dailyProgress->project
            ? $dailyProgress->project->items()->with('workItem')->get()
            : collect();

        return view('progress::daily-progress.edit', compact('dailyProgress', 'projects', 'employees', 'projectItems'));
    }

    /**
     * تحديث تقرير يومي
     */
    public function update(Request $request, DailyProgress $dailyProgress)
    {
        $validated = $request->validate([
            'quantity'      => 'required|numeric|min:0',
            'progress_date' => 'required|date',
            'notes'         => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            $item = $dailyProgress->projectItem;
            $diff = (float) $request->quantity - $dailyProgress->quantity;
            $newCompleted = $item->completed_quantity + $diff;
            $completionPercentage = $item->total_quantity > 0
                ? min(100, round(($newCompleted / $item->total_quantity) * 100, 2))
                : 0;

            $dailyProgress->update(array_merge($validated, [
                'completion_percentage' => $completionPercentage
            ]));
            $item->update([
                'completed_quantity'    => $newCompleted,
                'completion_percentage' => $completionPercentage
            ]);
            $this->updateProjectStatus($item->project_id);
            DB::commit();
            return redirect()->route('daily_progress.index')->with('success', 'تم تحديث التقدم اليومي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * حذف تقرير يومي
     */
    public function destroy(DailyProgress $dailyProgress)
    {
        try {
            DB::beginTransaction();
            $item = $dailyProgress->projectItem;
            $projectId = $item->project_id;
            $dailyProgress->delete();
            $newCompleted = $item->dailyProgress()->sum('quantity');
            $completionPercentage = $item->total_quantity > 0
                ? round(($newCompleted / $item->total_quantity) * 100, 2)
                : 0;
            $item->update([
                'completed_quantity'    => $newCompleted,
                'completion_percentage' => $completionPercentage
            ]);
            $this->updateProjectStatus($projectId);
            DB::commit();
            return redirect()->route('daily_progress.index')->with('success', 'تم حذف التسجيل اليومي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * تحديث حالة المشروع بناءً على إنجاز البنود
     */
    private function updateProjectStatus(int $projectId): void
    {
        $project = ProjectProgress::with('items')->find($projectId);
        if (!$project || !$project->items->count()) {
            return;
        }
        $totalItems = $project->items->count();
        $completedItems = $project->items->where('completion_percentage', '>=', 100)->count();
        $activeItems = $project->items->filter(fn ($i) => $i->completion_percentage > 0 && $i->completion_percentage < 100)->count();

        if ($completedItems >= $totalItems) {
            $project->update(['status' => 'completed']);
        } elseif ($activeItems > 0 || $completedItems > 0) {
            $project->update(['status' => 'in_progress']);
        } else {
            $project->update(['status' => 'pending']);
        }
    }
}
